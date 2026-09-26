#!/usr/bin/env bash
set -e

# ==============================================================================
# Rotina Noturna Automática de Processamento e Atualização - Monitora Fácil
# Execução agendada (padrão: 03:00 da manhã)
# Executa a sequência oficial de 20 etapas de sincronização, compilação e caches.
# ==============================================================================

PROJECT_DIR="/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com"

if [ -d "$PROJECT_DIR" ]; then
    cd "$PROJECT_DIR"
else
    cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
fi

mkdir -p storage/logs
LOG_FILE="storage/logs/nightly-sync.log"

START_TIME=$(date '+%Y-%m-%d %H:%M:%S')
echo "==============================================================================" | tee -a "$LOG_FILE"
echo "Iniciando Rotina Noturna Monitora Fácil em: $START_TIME" | tee -a "$LOG_FILE"
echo "Diretório de Execução: $(pwd)" | tee -a "$LOG_FILE"
echo "==============================================================================" | tee -a "$LOG_FILE"

# Detecção do binário PHP (preferência para php8.5 do CloudPanel)
if command -v php8.5 &> /dev/null; then
    PHP_BIN="php8.5"
else
    PHP_BIN="php"
fi

# Notifica início da rotina gravando no banco
$PHP_BIN artisan tinker --execute="app(\App\Services\SettingsService::class)->set('nightly_routine_last_run', '$START_TIME'); app(\App\Services\SettingsService::class)->set('nightly_routine_last_status', 'running');" 2>/dev/null || true

EXIT_STATUS="success"

{
    echo "==> [1/20] cd $PROJECT_DIR"
    cd "$PROJECT_DIR"

    echo "==> [2/20] git pull origin main"
    git pull origin main

    echo "==> [3/20] composer install --no-dev --optimize-autoloader"
    composer install --no-dev --optimize-autoloader

    echo "==> [4/20] npm run build"
    npm run build

    echo "==> [5/20] $PHP_BIN artisan migrate --force"
    $PHP_BIN artisan migrate --force

    echo "==> [6/20] $PHP_BIN -d memory_limit=1024M artisan cvat:sync-nominal"
    $PHP_BIN -d memory_limit=1024M artisan cvat:sync-nominal || echo "AVISO: Falha na sincronização nominal do CVAT. Continuando rotina..."

    echo "==> [7/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c1"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c1 || echo "AVISO: Falha na consolidação do C1. Continuando rotina..."

    echo "==> [8/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c2"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c2 || echo "AVISO: Falha na consolidação do C2. Continuando rotina..."

    echo "==> [9/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c3"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c3 || echo "AVISO: Falha na consolidação do C3. Continuando rotina..."

    echo "==> [10/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c4"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c4 || echo "AVISO: Falha na consolidação do C4. Continuando rotina..."

    echo "==> [11/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c5"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c5 || echo "AVISO: Falha na consolidação do C5. Continuando rotina..."

    echo "==> [12/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c6"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c6 || echo "AVISO: Falha na consolidação do C6. Continuando rotina..."

    echo "==> [13/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c7"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c7 || echo "AVISO: Falha na consolidação do C7. Continuando rotina..."

    echo "==> [14/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=oral-health"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=oral-health || echo "AVISO: Falha na consolidação de Saúde Bucal (--scope=oral-health). Continuando rotina..."

    echo "==> [15/20] $PHP_BIN -d memory_limit=1024M artisan esus:process-oral-health"
    $PHP_BIN -d memory_limit=1024M artisan esus:process-oral-health || echo "AVISO: Falha na consolidação de Saúde Bucal (esus:process-oral-health). Continuando rotina..."

    echo "==> [16/20] $PHP_BIN artisan livewire:publish --assets"
    $PHP_BIN artisan livewire:publish --assets

    echo "==> [17/20] $PHP_BIN artisan optimize:clear"
    $PHP_BIN artisan optimize:clear

    echo "==> [18/20] $PHP_BIN artisan config:cache"
    $PHP_BIN artisan config:cache

    echo "==> [19/20] $PHP_BIN artisan queue:restart || true"
    $PHP_BIN artisan queue:restart 2>&1 || true

    echo "==> [20/20] $PHP_BIN artisan route:cache && $PHP_BIN artisan view:cache"
    $PHP_BIN artisan route:cache
    $PHP_BIN artisan view:cache
} 2>&1 | tee -a "$LOG_FILE" || EXIT_STATUS="failed"

END_TIME=$(date '+%Y-%m-%d %H:%M:%S')
echo "==============================================================================" | tee -a "$LOG_FILE"
if [ "$EXIT_STATUS" = "success" ]; then
    echo "✔ Rotina Noturna finalizada com SUCESSO em: $END_TIME" | tee -a "$LOG_FILE"
    $PHP_BIN artisan tinker --execute="app(\App\Services\SettingsService::class)->set('nightly_routine_last_status', 'success'); app(\App\Services\SettingsService::class)->set('nightly_routine_finished_at', '$END_TIME');" 2>/dev/null || true
else
    echo "❌ Rotina Noturna finalizada com FALHAS em: $END_TIME" | tee -a "$LOG_FILE"
    $PHP_BIN artisan tinker --execute="app(\App\Services\SettingsService::class)->set('nightly_routine_last_status', 'failed'); app(\App\Services\SettingsService::class)->set('nightly_routine_finished_at', '$END_TIME');" 2>/dev/null || true
fi
echo "==============================================================================" | tee -a "$LOG_FILE"
