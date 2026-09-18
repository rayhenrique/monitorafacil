#!/usr/bin/env bash
# ==============================================================================
# Script de Deploy para Produção - Saúde Brasil 360 Monitor (MonitoraFácil)
# ==============================================================================
set -e

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "==> [1/7] Iniciando deploy em: $APP_DIR"

# 1. Ativa modo de manutenção (se a aplicação já estiver rodando)
echo "==> [2/7] Ativando modo de manutenção..."
php artisan down --retry=60 || true

# 2. Instalação e otimização das dependências PHP
echo "==> [3/7] Instalando dependências de produção do Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. Execução de migrações
echo "==> [4/7] Executando migrações no banco local (MySQL)..."
php artisan migrate --force

# 4. Build de assets frontend
echo "==> [5/7] Compilando assets do frontend (Vite)..."
if command -v npm &> /dev/null; then
    npm ci --no-audit --prefer-offline || npm install --no-audit
    npm run build
else
    echo "AVISO: npm não encontrado no PATH. Certifique-se de que os assets foram compilados previamente."
fi

# 5. Otimização de Caches do Laravel
echo "==> [6/7] Otimizando caches (configurações, rotas e views)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Ajuste de permissões de diretórios críticos
echo "==> [7/7] Ajustando permissões de storage e bootstrap/cache..."
chmod -R 775 storage bootstrap/cache || true

# 7. Finalização e desativação do modo de manutenção
php artisan up

echo "=============================================================================="
echo "✔ Deploy concluído com sucesso em $(date '+%d/%m/%Y %H:%M:%S')!"
echo "=============================================================================="
