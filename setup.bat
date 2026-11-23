@echo off
REM Setup sekali jalan untuk CBT Online (Windows).
REM Membuat .env, membangun aset frontend, lalu menyalakan Docker.
setlocal

IF NOT EXIST .env (
  copy .env.example .env >nul
  powershell -NoProfile -Command "$pw = -join ((48..57)+(65..90)+(97..122) | Get-Random -Count 16 | %% { [char]$_ }); (Get-Content .env) -replace '^DB_PASSWORD=.*$', ('DB_PASSWORD=' + $pw) | Set-Content .env"
  echo [OK] .env dibuat dengan password database acak.
) ELSE (
  echo [OK] .env sudah ada, dipakai yang sekarang.
)

echo [1/4] Install dependensi frontend...
call npm install
echo [2/4] Build aset frontend...
call npm run build

echo [3/4] Menyalakan Docker (migrate + seed otomatis)...
docker compose up -d --build

echo [4/4] Membuat storage link...
docker compose exec app php artisan storage:link

echo.
echo Selesai! Buka http://localhost:8080
