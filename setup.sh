#!/usr/bin/env bash
# Setup sekali jalan untuk CBT Online (Linux/Mac).
# Membuat .env, membangun aset frontend, lalu menyalakan Docker.
set -e
cd "$(dirname "$0")"

if [ ! -f .env ]; then
  cp .env.example .env
  PW="$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 16)"
  sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${PW}/" .env
  echo "[OK] .env dibuat dengan password database acak."
else
  echo "[OK] .env sudah ada, dipakai yang sekarang."
fi

echo "[1/4] Install dependensi frontend..."
npm install
echo "[2/4] Build aset frontend..."
npm run build

echo "[3/4] Menyalakan Docker (migrate + seed otomatis)..."
docker compose up -d --build

echo "[4/4] Membuat storage link..."
docker compose exec app php artisan storage:link || true

echo ""
echo "Selesai! Buka http://localhost:8080"
