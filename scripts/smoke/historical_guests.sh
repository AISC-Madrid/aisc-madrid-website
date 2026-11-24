#!/bin/sh
# Smoke test de la carga de guests históricos (#337): scripts/load_historical_guests.php.
# Se ejecuta EN EL VPS contra un contenedor de dev_container.sh (BD de DEV, nunca prod):
#   ssh root@VPS 'sh -s aisc-smoke' < scripts/smoke/historical_guests.sh
# Crea guests "smoke_*" y los borra al terminar. Usa los eventos 11 y 13, que existen en dev.
set -u

CONTAINER=${1:-aisc-smoke}
DEV_DB=ksg88ik9kggmuyvt5g1lgf33
LOADER=scripts/load_historical_guests.php
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
    docker exec "$CONTAINER" rm -f /tmp/smoke_guests.csv /tmp/smoke_bad.csv
    rm -f "$OUT"
}
trap cleanup EXIT

ok()   { echo "  ok   - $1"; }
fail() { echo "  FAIL - $1"; FAILS=$((FAILS + 1)); }
check() { if eval "$2"; then ok "$1"; else fail "$1"; fi; }
csv()  { docker exec -i "$CONTAINER" sh -c "cat > $1"; }
load() { docker exec "$CONTAINER" php "$LOADER" "$1" > "$OUT" 2>&1; }

delete_smoke_guests

# Cuenta compartida previa (como edu_alvaro en prod): activa y con el evento 11
sql "INSERT INTO guests (username, password_hash, name, is_active) VALUES ('smoke_shared', 'old-hash', 'Ana & Otra', 1);
     INSERT INTO event_guest_access (guest_id, event_id) SELECT id, 11 FROM guests WHERE username = 'smoke_shared';"
shared_id=$(sql "SELECT id FROM guests WHERE username = 'smoke_shared'")

csv /tmp/smoke_guests.csv <<'CSV'
username,name,email,phone,linkedin_url,event_ids,previous_username
smoke_ana,Ana Smoke,,,,11,smoke_shared
smoke_otra,Otra Smoke,,+34 600 00 00 01,,11,
smoke_bea,Bea Smoke,bea@example.com,+34 600 00 00 02,https://www.linkedin.com/in/bea,11;13,
smoke_carla,Carla Smoke,,,,13;99999999,
CSV

echo "Primera carga"
load /tmp/smoke_guests.csv; status=$?
check "la carga termina bien" "[ $status -eq 0 ]"
check "avisa del evento que no existe" "grep -q 99999999 '$OUT'"
check "la cuenta compartida ya no existe" "[ \"\$(sql \"SELECT COUNT(*) FROM guests WHERE username = 'smoke_shared'\")\" = 0 ]"
check "el primer guest hereda el id de la cuenta compartida" "[ \"\$(sql \"SELECT id FROM guests WHERE username = 'smoke_ana'\")\" = '$shared_id' ]"
check "la cuenta separada cambia de contraseña" "[ \"\$(sql \"SELECT password_hash <> 'old-hash' FROM guests WHERE username = 'smoke_ana'\")\" = 1 ]"
check "la cuenta separada queda con un nombre de una sola persona" "[ \"\$(sql \"SELECT name FROM guests WHERE username = 'smoke_ana'\")\" = 'Ana Smoke' ]"
check "los dos guests de la cuenta compartida tienen el evento 11" "[ \"\$(sql \"SELECT COUNT(*) FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username IN ('smoke_ana','smoke_otra') AND a.event_id = 11\")\" = 2 ]"
check "hay 4 guests smoke, todos inactivos" "[ \"\$(sql \"SELECT CONCAT(COUNT(*), '/', SUM(is_active)) FROM guests WHERE username LIKE 'smoke\\_%'\")\" = '4/0' ]"
bcrypt_count=$(sql "SELECT COUNT(DISTINCT password_hash) FROM guests WHERE username LIKE 'smoke\_%' AND LEFT(password_hash, 4) = CONCAT(CHAR(36), '2y', CHAR(36))")
check "las contraseñas son hashes bcrypt distintos" '[ "$bcrypt_count" = 4 ]'
check "guarda el contacto" "[ \"\$(sql \"SELECT CONCAT_WS('|', email, phone, linkedin_url) FROM guests WHERE username = 'smoke_bea'\")\" = 'bea@example.com|+34 600 00 00 02|https://www.linkedin.com/in/bea' ]"
check "un guest con varios eventos queda asociado a todos" "[ \"\$(sql \"SELECT GROUP_CONCAT(a.event_id ORDER BY a.event_id) FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username = 'smoke_bea'\")\" = '11,13' ]"
check "el evento inexistente se ignora y el resto se asocia" "[ \"\$(sql \"SELECT GROUP_CONCAT(a.event_id) FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username = 'smoke_carla'\")\" = '13' ]"
check "no imprime contraseñas" "! grep -qi 'password' '$OUT'"

hashes() { sql "SELECT GROUP_CONCAT(CONCAT(username, ':', password_hash) ORDER BY username) FROM guests WHERE username LIKE 'smoke\_%'"; }
hashes_before=$(hashes)
links_before=$(sql "SELECT COUNT(*) FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username LIKE 'smoke\_%'")

# Un admin reactiva a un guest y le rellena el LinkedIn a mano: la recarga no lo pisa
sql "UPDATE guests SET is_active = 1, linkedin_url = 'https://linkedin.com/in/otra' WHERE username = 'smoke_otra'"

echo "Segunda carga (re-ejecución)"
load /tmp/smoke_guests.csv; status=$?
check "la re-ejecución termina bien" "[ $status -eq 0 ]"
check "no duplica guests" "[ \"\$(sql \"SELECT COUNT(*) FROM guests WHERE username LIKE 'smoke\\_%'\")\" = 4 ]"
check "no duplica asociaciones" "[ \"\$(sql \"SELECT COUNT(*) FROM event_guest_access a JOIN guests g ON g.id = a.guest_id WHERE g.username LIKE 'smoke\\_%'\")\" = '$links_before' ]"
check "no cambia contraseñas de guests ya cargados" '[ "$(hashes)" = "$hashes_before" ]'
check "respeta la reactivación manual" "[ \"\$(sql \"SELECT is_active FROM guests WHERE username = 'smoke_otra'\")\" = 1 ]"
check "un campo vacío en el CSV no borra el dato guardado" "[ \"\$(sql \"SELECT linkedin_url FROM guests WHERE username = 'smoke_otra'\")\" = 'https://linkedin.com/in/otra' ]"

echo "CSV con errores"
csv /tmp/smoke_bad.csv <<'CSV'
username,name,email,phone,linkedin_url,event_ids,previous_username
smoke_good,Good Smoke,,,,11,
smoke_bad,Bad Smoke,no-es-un-email,,,11,
CSV
load /tmp/smoke_bad.csv; status=$?
check "un email inválido hace fallar la carga" "[ $status -ne 0 ]"
check "y no se escribe nada" "[ \"\$(sql \"SELECT COUNT(*) FROM guests WHERE username IN ('smoke_good','smoke_bad')\")\" = 0 ]"

echo
if [ "$FAILS" -eq 0 ]; then echo "Todo OK"; else echo "$FAILS fallos"; exit 1; fi
