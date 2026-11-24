#!/bin/sh
# Contenedor efímero para probar una rama contra la BD de DEV, sin tocar la app de dev.
# Se ejecuta EN EL VPS. Usa la imagen y el entorno de la app de dev (SMTP_PASS vacío para no enviar
# correos), sobrescribe el código con los archivos de la rama y añade una sesión de admin falsa
# (_test_login.php) que solo existe dentro del contenedor.
#
#   sh dev_container.sh up   <nombre> <puerto> <tar con los archivos de la rama>
#   sh dev_container.sh down <nombre>
#
# Desde la máquina local:
#   git archive HEAD | ssh root@VPS 'cat > /tmp/aisc-branch.tar'
#   ssh root@VPS 'sh -s up aisc-smoke 18080 /tmp/aisc-branch.tar' < scripts/smoke/dev_container.sh
set -eu

DEV_APP=n397ichqwyx888f1fjoo1hv6
DEV_DB=ksg88ik9kggmuyvt5g1lgf33

cmd=${1:?up|down}
name=${2:?nombre del contenedor}

if [ "$cmd" = down ]; then
    docker rm -f "$name" >/dev/null 2>&1 || true
    exit 0
fi

port=${3:?puerto}
tar=${4:?tar de la rama}

app=$(docker ps --filter "name=^${DEV_APP}" --format '{{.Names}}' | head -n1)
[ -n "$app" ] || { echo "No encuentro el contenedor de la app de dev" >&2; exit 1; }
image=$(docker inspect -f '{{.Config.Image}}' "$app")

envfile=$(mktemp)
docker inspect -f '{{range .Config.Env}}{{println .}}{{end}}' "$app" | grep -v '^$' | grep -v '^SMTP_PASS=' > "$envfile"
echo "SMTP_PASS=" >> "$envfile"

# Salvaguarda: el contenedor tiene que apuntar a la BD de dev
grep -q "^DB_HOST=${DEV_DB}$" "$envfile" || { echo "DB_HOST no es la BD de dev; aborto" >&2; rm -f "$envfile"; exit 1; }

docker rm -f "$name" >/dev/null 2>&1 || true
docker create --name "$name" --network coolify --env-file "$envfile" -p "127.0.0.1:${port}:80" "$image" >/dev/null
rm -f "$envfile"

docker cp - "$name":/var/www/html < "$tar"
login=$(mktemp)
cat > "$login" <<'PHP'
<?php
session_start();
$_SESSION['user_id'] = 0;
$_SESSION['name'] = 'Smoke';
$_SESSION['activated'] = true;
$_SESSION['role'] = 'admin';
echo 'ok';
PHP
chmod 644 "$login"
docker cp "$login" "$name":/var/www/html/_test_login.php
rm -f "$login"

docker start "$name" >/dev/null
for _ in 1 2 3 4 5 6 7 8 9 10; do
    [ "$(curl -s "http://127.0.0.1:${port}/_test_login.php" 2>/dev/null)" = ok ] && { echo "up: http://127.0.0.1:${port}"; exit 0; }
    sleep 1
done
echo "El contenedor no responde" >&2
exit 1
