#!/bin/sh
set -eu

# Env (с дефолтами)
: "${PGADMIN_SERVER_NAME:=app}"
: "${PGADMIN_SERVER_GROUP:=Servers}"

: "${DB_HOST:=postgres}"
: "${DB_PORT:=5432}"
: "${DB_USERNAME:=postgres}"
: "${DB_PASSWORD:=secret}"
: "${DB_DATABASE:=postgres}"

# Куда писать
SERVERS_JSON_PATH="${PGADMIN_SERVER_JSON_FILE:-/pgadmin4/servers.json}"

mkdir -p "$(dirname "$SERVERS_JSON_PATH")"

cat > "$SERVERS_JSON_PATH" <<EOF
{
  "Servers": {
    "1": {
      "Name": "$(printf "%s" "$PGADMIN_SERVER_NAME" | sed 's/"/\\"/g')",
      "Group": "$(printf "%s" "$PGADMIN_SERVER_GROUP" | sed 's/"/\\"/g')",
      "Host": "$(printf "%s" "$DB_HOST" | sed 's/"/\\"/g')",
      "Port": $(printf "%s" "$DB_PORT" | sed 's/[^0-9]//g'),
      "MaintenanceDB": "$(printf "%s" "$DB_DATABASE" | sed 's/"/\\"/g')",
      "Username": "$(printf "%s" "$DB_USERNAME" | sed 's/"/\\"/g')",
      "SSLMode": "prefer"
    }
  }
}
EOF

# (Опционально, но удобно) pgpass, чтобы не вводить пароль руками
# pgAdmin сам pgpass не создаёт — делаем мы.
PGPASS_PATH="${PGPASSFILE:-/pgpass}"
cat > "$PGPASS_PATH" <<EOF
${DB_HOST}:${DB_PORT}:*:${DB_USERNAME}:${DB_PASSWORD}
EOF
chmod 600 "$PGPASS_PATH" 2>/dev/null || true

echo "[pgadmin-init] Wrote: $SERVERS_JSON_PATH"
echo "[pgadmin-init] Wrote: $PGPASS_PATH"
