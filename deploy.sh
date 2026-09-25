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

echo "==> [1/18] Atualizando repositório a partir da branch main..."
git pull origin main

echo "==> [2/18] Instalando dependências de produção do Composer..."
composer install --no-dev --optimize-autoloader

echo "==> [3/18] Compilando assets do frontend (Vite)..."
npm run build

# Detecção do binário PHP (preferência para php8.5 do CloudPanel)
if command -v php8.5 &> /dev/null; then
    PHP_BIN="php8.5"
else
    PHP_BIN="php"
fi

echo "==> [4/18] Executando migrações do banco de dados..."
$PHP_BIN artisan migrate --force

if [ "$SYNC_DATA" = true ]; then
    echo "==> [5/18] Sincronizando dados nominais do PEC (CVAT Relação Nominal)..."
    $PHP_BIN -d memory_limit=1024M artisan cvat:sync-nominal || echo "AVISO: Falha na sincronização nominal do CVAT. Continuando deploy..."

    echo "==> [6/18] Consolidando dados reais do Indicador C1 (Mais Acesso)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c1 || echo "AVISO: Falha na consolidação do C1. Continuando deploy..."

    echo "==> [7/18] Consolidando dados reais do Indicador C2 (Desenvolvimento Infantil)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c2 || echo "AVISO: Falha na consolidação do C2. Continuando deploy..."

    echo "==> [8/18] Consolidando dados reais do Indicador C3 (Gestação e Puerpério)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c3 || echo "AVISO: Falha na consolidação do C3. Continuando deploy..."

    echo "==> [9/18] Consolidando dados reais do Indicador C4 (Pessoas com Diabetes)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c4 || echo "AVISO: Falha na consolidação do C4. Continuando deploy..."

    echo "==> [10/18] Consolidando dados reais do Indicador C5 (Pessoas com Hipertensão)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c5 || echo "AVISO: Falha na consolidação do C5. Continuando deploy..."

    echo "==> [11/19] Consolidando dados reais do Indicador C6 (Cuidado da Pessoa Idosa)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c6 || echo "AVISO: Falha na consolidação do C6. Continuando deploy..."

    echo "==> [12/19] Consolidando dados reais do Indicador C7 (Cuidado da Mulher na Prevenção do Câncer)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-data --scope=c7 || echo "AVISO: Falha na consolidação do C7. Continuando deploy..."

    echo "==> [13/19] Consolidando dados reais de Saúde Bucal (Indicadores B1 a B6 - eSB)..."
    $PHP_BIN -d memory_limit=1024M artisan esus:process-oral-health || echo "AVISO: Falha na consolidação de Saúde Bucal. Continuando deploy..."
else
    echo "==> [5-13/19] Sincronização de dados do PEC ignorada (--quick / --no-sync ativo)."
fi

echo "==> [14/19] Publicando assets do Livewire..."
$PHP_BIN artisan livewire:publish --assets

echo "==> [15/19] Limpando caches da aplicação..."
$PHP_BIN artisan optimize:clear

echo "==> [16/19] Otimizando cache de configuração..."
$PHP_BIN artisan config:cache

echo "==> [17/19] Reiniciando workers da fila com a versão nova..."
$PHP_BIN artisan queue:restart || true
if command -v systemctl &> /dev/null && ! systemctl is-active --quiet monitorafacil-queue.service; then
    echo "ATENÇÃO: monitorafacil-queue.service não está ativo. O botão CVAT agenda jobs, mas eles precisam de um worker para executar." >&2
    echo "Veja scripts/monitorafacil-queue.service.example e as instruções no README.md." >&2
fi

echo "==> [18/19] Otimizando cache de rotas..."
$PHP_BIN artisan route:cache

echo "==> [19/19] Otimizando cache de views..."
$PHP_BIN artisan view:cache

echo "=============================================================================="
echo "✔ Deploy concluído com sucesso!"
echo "=============================================================================="
