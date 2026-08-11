#!/usr/bin/env bash
# Automated MySQL dump for Clubinho Pet Box (Docker Compose).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

KEEP="${BACKUP_KEEP:-14}"
OUT_DIR="${BACKUP_DIR:-$ROOT/storage/backups}"
STAMP="$(date +%Y%m%d-%H%M%S)"
FILE="$OUT_DIR/clubinhopet-$STAMP.sql.gz"

mkdir -p "$OUT_DIR"

if ! docker compose ps --status running --services 2>/dev/null | grep -qx db; then
  echo "error: serviço db não está rodando. Suba com: make up" >&2
  exit 1
fi

echo "→ dump para $FILE"
docker compose exec -T db mysqldump \
  -uclubinho -pclubinho \
  --single-transaction \
  --routines \
  --triggers \
  --no-tablespaces \
  clubinhopet | gzip -c > "$FILE"

# Keep only the newest N dumps
mapfile -t OLD < <(ls -1t "$OUT_DIR"/clubinhopet-*.sql.gz 2>/dev/null | tail -n +"$((KEEP + 1))" || true)
if ((${#OLD[@]})); then
  echo "→ removendo $((${#OLD[@]})) backup(s) antigos (keep=$KEEP)"
  rm -f "${OLD[@]}"
fi

ls -lh "$FILE"
echo "ok"
