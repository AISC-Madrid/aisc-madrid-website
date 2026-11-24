#!/bin/sh
# Smoke test HTTP de los datos de contacto de los guests (#335).
# Se ejecuta EN EL VPS contra un contenedor de dev_container.sh (BD de DEV, nunca prod):
#   ssh root@VPS 'sh -s 18080' < scripts/smoke/guests_contact.sh
# Crea guests "smoke_*" y los borra al terminar.
set -u

PORT=${1:-18080}
BASE="http://127.0.0.1:${PORT}/dashboard/guests"
DEV_DB=ksg88ik9kggmuyvt5g1lgf33
JAR=$(mktemp)
OUT=$(mktemp)
FAILS=0

sql() {
    docker exec -i "$DEV_DB" sh -c 'mariadb -N -B -uroot -p"$MARIADB_ROOT_PASSWORD" aisc' <<SQL
$1
SQL
}
delete_smoke_guests() {
    sql "DELETE a FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username LIKE 'smoke\_%'; DELETE FROM guests WHERE username LIKE 'smoke\_%';" >/dev/null
}
cleanup() {
    delete_smoke_guests
    rm -f "$JAR" "$OUT"
}
trap cleanup EXIT

ok()   { echo "  ok   - $1"; }
fail() { echo "  FAIL - $1"; FAILS=$((FAILS + 1)); }
check() { if eval "$2"; then ok "$1"; else fail "$1"; fi; }
has()   { grep -qF -- "$1" "$OUT"; }
# El atributo checked va en la línea siguiente al id del checkbox
event_checked() { grep -A1 -F "id=\"event_$1\"" "$OUT" | grep -q 'checked'; }
redirects_to() { case "$CODE" in "302 "*"$1") return 0;; esac; return 1; }

# POST de un formulario; deja el HTML en $OUT y en $CODE el código HTTP y el destino de la redirección
post() {
    url=$1; shift
    CODE=$(curl -s --max-time 20 -o "$OUT" -w '%{http_code} %{redirect_url}' -b "$JAR" -c "$JAR" "$@" "$url")
}
get() {
    CODE=$(curl -s --max-time 20 -o "$OUT" -w '%{http_code}' -b "$JAR" -c "$JAR" "$1")
}
guest_id() { sql "SELECT id FROM guests WHERE username = '$1'"; }
guest_row() { sql "SELECT IFNULL(email,'NULL'), IFNULL(phone,'NULL'), IFNULL(linkedin_url,'NULL') FROM guests WHERE username = '$1'" | tr '\t' '|'; }

delete_smoke_guests
EVENT_ID=$(sql "SELECT id FROM events WHERE status = 'published' ORDER BY id LIMIT 1")
[ "$(curl -s -b "$JAR" -c "$JAR" "http://127.0.0.1:${PORT}/_test_login.php")" = ok ] || { echo "No hay sesión de admin falsa"; exit 1; }

echo "Crear guest"
post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_full --data-urlencode password=secret123 --data-urlencode name='Smoke Full' \
    --data-urlencode email=' ana@example.com ' --data-urlencode phone='+34 600 000 000' \
    --data-urlencode linkedin_url='https://www.linkedin.com/in/ana'
check "redirige a la lista tras crear" 'redirects_to "guests_list.php?created=1"'
check "guarda email, teléfono y LinkedIn recortados" '[ "$(guest_row smoke_full)" = "ana@example.com|+34 600 000 000|https://www.linkedin.com/in/ana" ]'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_empty --data-urlencode password=secret123 \
    --data-urlencode email='  ' --data-urlencode phone='' --data-urlencode linkedin_url=''
check "los campos vacíos quedan como NULL" '[ "$(guest_row smoke_empty)" = "NULL|NULL|NULL" ]'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_bademail --data-urlencode password=secret123 --data-urlencode name='Nombre Escrito' \
    --data-urlencode email='no-es-un-email' --data-urlencode phone='612345678' \
    --data-urlencode linkedin_url='https://linkedin.com/in/x' --data-urlencode "events[]=$EVENT_ID"
check "email inválido: no se crea el guest" '[ "$CODE" = "200 " ] && [ -z "$(guest_id smoke_bademail)" ]'
check "email inválido: muestra el error" 'has "El email no tiene un formato válido."'
check "email inválido: conserva lo escrito y los eventos marcados" 'has "value=\"no-es-un-email\"" && has "value=\"612345678\"" && has "value=\"https://linkedin.com/in/x\"" && has "value=\"Nombre Escrito\"" && event_checked "$EVENT_ID"'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_longemail --data-urlencode password=secret123 \
    --data-urlencode email="$(printf 'a%.0s' $(seq 1 64))@$(printf 'b%.0s' $(seq 1 63)).$(printf 'c%.0s' $(seq 1 63)).$(printf 'd%.0s' $(seq 1 63)).com"
check "email de más de 254 caracteres: se rechaza" '[ -z "$(guest_id smoke_longemail)" ] && has "El email no tiene un formato válido."'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_badlinkedin --data-urlencode password=secret123 \
    --data-urlencode linkedin_url='https://evil-linkedin.com/in/x'
check "LinkedIn de otro dominio: no se crea y muestra el error" '[ -z "$(guest_id smoke_badlinkedin)" ] && has "El LinkedIn debe ser una URL de linkedin.com."'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_subdomain --data-urlencode password=secret123 \
    --data-urlencode linkedin_url='http://es.linkedin.com/in/x'
check "LinkedIn de un subdominio de linkedin.com: se acepta" '[ "$(guest_row smoke_subdomain)" = "NULL|NULL|http://es.linkedin.com/in/x" ]'

post "$BASE/create_guest.php" \
    --data-urlencode username=smoke_longphone --data-urlencode password=secret123 \
    --data-urlencode phone="$(printf '1%.0s' $(seq 1 31))"
check "teléfono de más de 30 caracteres: se rechaza" '[ -z "$(guest_id smoke_longphone)" ] && has "El teléfono no puede superar los 30 caracteres."'

echo "Editar guest"
ID=$(guest_id smoke_full)
get "$BASE/edit_guest.php?id=$ID"
check "muestra los valores guardados" 'has "value=\"ana@example.com\"" && has "value=\"+34 600 000 000\"" && has "value=\"https://www.linkedin.com/in/ana\""'

post "$BASE/edit_guest.php?id=$ID" \
    --data-urlencode username=smoke_full --data-urlencode name='Smoke Full' --data-urlencode is_active=1 \
    --data-urlencode email='nuevo@example.com' --data-urlencode phone='' --data-urlencode linkedin_url='https://linkedin.com/in/nuevo'
check "cambia y vacía los datos de contacto" 'redirects_to "guests_list.php?updated=1" && [ "$(guest_row smoke_full)" = "nuevo@example.com|NULL|https://linkedin.com/in/nuevo" ]'

post "$BASE/edit_guest.php?id=$ID" \
    --data-urlencode username=smoke_full --data-urlencode name='Smoke Full' --data-urlencode is_active=1 \
    --data-urlencode email='otro@example.com' --data-urlencode phone='699' --data-urlencode linkedin_url='ftp://linkedin.com/in/x' \
    --data-urlencode "events[]=$EVENT_ID"
check "LinkedIn inválido al editar: no guarda nada" '[ "$CODE" = "200 " ] && [ "$(guest_row smoke_full)" = "nuevo@example.com|NULL|https://linkedin.com/in/nuevo" ]'
check "LinkedIn inválido al editar: mismo error, conserva lo escrito y los eventos marcados" 'has "El LinkedIn debe ser una URL de linkedin.com." && has "value=\"otro@example.com\"" && has "value=\"699\"" && has "value=\"ftp://linkedin.com/in/x\"" && event_checked "$EVENT_ID"'

echo
if [ "$FAILS" -eq 0 ]; then echo "OK"; else echo "$FAILS fallo(s)"; exit 1; fi
