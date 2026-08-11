#!/bin/bash
set -euo pipefail

PORT="${PORT:-80}"

# Railway (and similar) inject PORT; Apache image defaults to 80.
if [[ -f /etc/apache2/ports.conf ]]; then
  sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
fi
if [[ -f /etc/apache2/sites-available/000-default.conf ]]; then
  sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

mkdir -p /var/www/html/public/uploads/happy \
         /var/www/html/public/uploads/testimonials \
         /var/www/html/storage/backups
chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage || true

# First boot: import full schema when users table is missing.
if [[ -n "${DB_HOST:-}" && -n "${DB_NAME:-}" && -n "${DB_USER:-}" ]]; then
  echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
  for i in $(seq 1 40); do
    if MYSQL_PWD="${DB_PASS:-}" mysqladmin ping \
        -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USER}" --silent 2>/dev/null; then
      break
    fi
    sleep 2
  done

  TABLES="$(MYSQL_PWD="${DB_PASS:-}" mysql -N -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USER}" \
      "${DB_NAME}" -e "SHOW TABLES LIKE 'users';" 2>/dev/null || true)"
  if [[ -z "${TABLES}" ]]; then
    SCHEMA="/var/www/html/database/docker/01_schema.sql"
    if [[ -f "${SCHEMA}" ]]; then
      echo "Importing schema into ${DB_NAME}..."
      MYSQL_PWD="${DB_PASS:-}" mysql -h"${DB_HOST}" -P"${DB_PORT:-3306}" -u"${DB_USER}" \
        "${DB_NAME}" < "${SCHEMA}"
      echo "Schema import done."
    else
      echo "WARNING: ${SCHEMA} not found; skipping import."
    fi
  else
    echo "Database already initialized (users table present)."
  fi
fi

exec "$@"
