#!/bin/sh
# Reference production entrypoint — the SQLite data plane:
#   1. restore-if-empty: litestream restores the tenant DB from object
#      storage when the local file is absent (fresh volume / re-provision);
#   2. migrate --force (idempotent — nothing pending is a no-op);
#   3. serve FrankenPHP under litestream replication (the only data path).
set -eu

DB_PATH="${DB_DATABASE:?DB_DATABASE must be set (e.g. /data/database.sqlite)}"
mkdir -p "$(dirname "$DB_PATH")"

if [ ! -s "$DB_PATH" ]; then
  echo "entrypoint: no database at $DB_PATH — restoring from Litestream if a replica exists"
  if litestream restore -config /etc/litestream.yml "$DB_PATH" 2>/dev/null; then
    echo "entrypoint: restored $DB_PATH from object storage"
  else
    echo "entrypoint: no replica yet — starting fresh; migrations create $DB_PATH"
  fi
else
  echo "entrypoint: database present at $DB_PATH — skipping restore"
fi

echo "entrypoint: running artisan migrations"
php artisan migrate --force

echo "entrypoint: serving FrankenPHP under Litestream replication"
# The stock image's canonical invocation: without --config the Caddyfile's
# {$SERVER_NAME} server block never loads and nothing listens on :80.
exec litestream replicate -config /etc/litestream.yml -exec "frankenphp run --config /etc/caddy/Caddyfile"
