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

echo "==> [1/11] Atualizando repositório a partir da branch main..."
git pull origin main

echo "==> [2/11] Instalando dependências de produção do Composer..."
composer install --no-dev --optimize-autoloader

echo "==> [3/11] Compilando assets do frontend (Vite)..."
npm run build

# Detecção do binário PHP (preferência para php8.5 do CloudPanel)
if command -v php8.5 &> /dev/null; then
    PHP_BIN="php8.5"
else
    PHP_BIN="php"
fi

echo "==> [4/11] Executando migrações do banco de dados..."
$PHP_BIN artisan migrate --force

echo "==> [5/11] Sincronizando dados nominais do PEC (CVAT Relação Nominal)..."
$PHP_BIN artisan cvat:sync-nominal

echo "==> [6/11] Publicando assets do Livewire..."
$PHP_BIN artisan livewire:publish --assets

echo "==> [7/11] Limpando caches da aplicação..."
$PHP_BIN artisan optimize:clear

echo "==> [8/11] Otimizando cache de configuração..."
$PHP_BIN artisan config:cache

echo "==> [9/11] Reiniciando workers da fila com a versão nova..."
$PHP_BIN artisan queue:restart
if command -v systemctl &> /dev/null && ! systemctl is-active --quiet monitorafacil-queue.service; then
    echo "ATENÇÃO: monitorafacil-queue.service não está ativo. O botão CVAT agenda jobs, mas eles precisam de um worker para executar." >&2
    echo "Veja scripts/monitorafacil-queue.service.example e as instruções no README.md." >&2
fi

echo "==> [10/11] Otimizando cache de rotas..."
$PHP_BIN artisan route:cache

echo "==> [11/11] Otimizando cache de views..."
$PHP_BIN artisan view:cache

echo "=============================================================================="
echo "✔ Deploy concluído com sucesso!"
echo "=============================================================================="
