#!/usr/bin/env bash
set -e

# ==============================================================================
# Script de Deploy e Atualização Contínua - Monitora Fácil
# ==============================================================================

PROJECT_DIR="/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com"

if [ -d "$PROJECT_DIR" ]; then
    cd "$PROJECT_DIR"
else
    # Fallback caso executado a partir de outro diretório relativo
    cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
fi

SYNC_DATA=true
for arg in "$@"; do
    if [ "$arg" = "--quick" ] || [ "$arg" = "--no-sync" ]; then
        SYNC_DATA=false
    fi
done

echo "==> [1/20] Atualizando repositório a partir da branch main..."
git pull origin main

echo "==> [2/20] Instalando dependências de produção do Composer..."
composer install --no-dev --optimize-autoloader

echo "==> [3/20] Compilando assets do frontend (Vite)..."
npm run build

# Detecção do binário PHP (preferência para php8.5 do CloudPanel)
if command -v php8.5 &> /dev/null; then
    PHP_BIN="php8.5"
else
    PHP_BIN="php"
fi

echo "==> [4/20] Executando migrações do banco de dados..."
$PHP_BIN artisan migrate --force

if [ "$SYNC_DATA" = true ]; then
    echo "==> [5/20] Sincronizando dados nominais do PEC (CVAT Relação Nominal)..."
    $PHP_BIN -d memory_limit=1024M artisan cvat:sync-nominal || echo "AVISO: Falha na sincronização nominal do CVAT. Continuando deploy..."

    echo "==> [6/20] Consolidando dados reais do Indicador C1 (Mais Acesso)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c1 || echo "AVISO: Falha na consolidação do C1. Continuando deploy..."

    echo "==> [7/20] Consolidando dados reais do Indicador C2 (Desenvolvimento Infantil)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c2 || echo "AVISO: Falha na consolidação do C2. Continuando deploy..."

    echo "==> [8/20] Consolidando dados reais do Indicador C3 (Gestação e Puerpério)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c3 || echo "AVISO: Falha na consolidação do C3. Continuando deploy..."

    echo "==> [9/20] Consolidando dados reais do Indicador C4 (Pessoas com Diabetes)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c4 || echo "AVISO: Falha na consolidação do C4. Continuando deploy..."

    echo "==> [10/20] Consolidando dados reais do Indicador C5 (Pessoas com Hipertensão)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c5 || echo "AVISO: Falha na consolidação do C5. Continuando deploy..."

    echo "==> [11/20] Consolidando dados reais do Indicador C6 (Cuidado da Pessoa Idosa)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c6 || echo "AVISO: Falha na consolidação do C6. Continuando deploy..."

    echo "==> [12/20] Consolidando dados reais do Indicador C7 (Cuidado da Mulher na Prevenção do Câncer)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c7 || echo "AVISO: Falha na consolidação do C7. Continuando deploy..."

    echo "==> [13/20] Consolidando dados reais de Saúde Bucal via esus:process-data (--scope=oral-health)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=oral-health || echo "AVISO: Falha na consolidação de Saúde Bucal (--scope=oral-health). Continuando deploy..."

    echo "==> [14/20] Consolidando dados reais de Saúde Bucal via esus:process-oral-health (Snapshots e Nominal)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-oral-health || echo "AVISO: Falha na consolidação de Saúde Bucal (esus:process-oral-health). Continuando deploy..."
else
    echo "==> [5-14/20] Sincronização de dados do PEC ignorada (--quick / --no-sync ativo)."
fi

echo "==> [15/20] Publicando assets do Livewire..."
$PHP_BIN artisan livewire:publish --assets

echo "==> [16/20] Limpando caches da aplicação..."
$PHP_BIN artisan optimize:clear

echo "==> [17/20] Otimizando cache de configuração..."
$PHP_BIN artisan config:cache

echo "==> [18/20] Reiniciando workers da fila com a versão nova..."
$PHP_BIN artisan queue:restart || true
if command -v systemctl &> /dev/null && ! systemctl is-active --quiet monitorafacil-queue.service; then
    echo "ATENÇÃO: monitorafacil-queue.service não está ativo. O botão CVAT agenda jobs, mas eles precisam de um worker para executar." >&2
    echo "Veja scripts/monitorafacil-queue.service.example e as instruções no README.md." >&2
fi

echo "==> [19/20] Otimizando cache de rotas..."
$PHP_BIN artisan route:cache

echo "==> [20/20] Otimizando cache de views..."
$PHP_BIN artisan view:cache

echo "=============================================================================="
echo "✔ Deploy concluído com sucesso!"
echo "=============================================================================="
