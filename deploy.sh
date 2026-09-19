#!/usr/bin/env bash
set -e

# ==============================================================================
# Atalho de Deploy - Executa o script oficial em scripts/deploy.sh
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SCRIPT_DIR/scripts/deploy.sh" ]; then
    bash "$SCRIPT_DIR/scripts/deploy.sh" "$@"
else
    cd /home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com
    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm run build
    php8.5 artisan migrate --force
    php8.5 artisan livewire:publish --assets
    php8.5 artisan optimize:clear
    php8.5 artisan config:cache
    php8.5 artisan route:cache
    php8.5 artisan view:cache
fi
