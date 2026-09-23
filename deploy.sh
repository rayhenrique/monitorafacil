#!/usr/bin/env bash
set -e

# ==============================================================================
# Atalho de Deploy - Executa o script oficial em scripts/deploy.sh
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SCRIPT_DIR/scripts/deploy.sh" ]; then
    exec bash "$SCRIPT_DIR/scripts/deploy.sh" "$@"
fi

# Fallback caso scripts/deploy.sh não seja encontrado
PROJECT_DIR="/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com"
if [ -d "$PROJECT_DIR" ]; then
    cd "$PROJECT_DIR"
else
    cd "$SCRIPT_DIR"
fi

SYNC_DATA=true
for arg in "$@"; do
    if [ "$arg" = "--quick" ] || [ "$arg" = "--no-sync" ]; then
        SYNC_DATA=false
    fi
done

echo "==> [1/13] Atualizando repositório a partir da branch main..."
git pull origin main

echo "==> [2/13] Instalando dependências de produção do Composer..."
composer install --no-dev --optimize-autoloader

echo "==> [3/13] Compilando assets do frontend (Vite)..."
npm run build

# Detecção do binário PHP (preferência para php8.5 do CloudPanel)
if command -v php8.5 &> /dev/null; then
    PHP_BIN="php8.5"
else
    PHP_BIN="php"
fi

echo "==> [4/13] Executando migrações do banco de dados..."
$PHP_BIN artisan migrate --force

if [ "$SYNC_DATA" = true ]; then
    echo "==> [5/13] Sincronizando dados nominais do PEC (CVAT Relação Nominal)..."
    $PHP_BIN artisan cvat:sync-nominal || echo "AVISO: Falha na sincronização nominal do CVAT. Continuando deploy..."

    echo "==> [6/13] Consolidando dados reais do Indicador C1 (Mais Acesso)..."
    $PHP_BIN artisan esus:process-data --scope=c1 || echo "AVISO: Falha na consolidação do C1. Continuando deploy..."

    echo "==> [7/13] Consolidando dados reais do Indicador C2 (Desenvolvimento Infantil)..."
    $PHP_BIN artisan esus:process-data --scope=c2 || echo "AVISO: Falha na consolidação do C2. Continuando deploy..."
else
    echo "==> [5-7/13] Sincronização de dados do PEC ignorada (--quick / --no-sync ativo)."
fi

echo "==> [8/13] Publicando assets do Livewire..."
$PHP_BIN artisan livewire:publish --assets

echo "==> [9/13] Limpando caches da aplicação..."
$PHP_BIN artisan optimize:clear

echo "==> [10/13] Otimizando cache de configuração..."
$PHP_BIN artisan config:cache

echo "==> [11/13] Reiniciando workers da fila com a versão nova..."
$PHP_BIN artisan queue:restart || true

echo "==> [12/13] Otimizando cache de rotas..."
$PHP_BIN artisan route:cache

echo "==> [13/13] Otimizando cache de views..."
$PHP_BIN artisan view:cache

echo "=============================================================================="
echo "✔ Deploy concluído com sucesso!"
echo "=============================================================================="
