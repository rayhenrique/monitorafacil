# Guia de Implantação e Operação em Produção (VPS)

Este documento fornece as instruções completas para implantar e operar o **Saúde Brasil 360 Monitor** em uma máquina virtual dedicada (VPS) ou servidor on-premise da Secretaria Municipal de Saúde.

---

## 1. Requisitos do Servidor

* **Sistema Operacional:** Ubuntu 22.04 / 24.04 LTS ou Debian 12
* **Hardware Mínimo Recomendado:**
  * 2 vCPUs
  * 4 GB de Memória RAM (2 GB mínimos se houver swap)
  * 20 GB de armazenamento SSD
* **Pilhas e Serviços:**
  * **PHP 8.3+** com extensões:
    `php-cli`, `php-fpm`, `php-mysql`, `php-pgsql`, `php-xml`, `php-curl`, `php-mbstring`, `php-bcmath`, `php-zip`, `php-intl`
  * **MySQL 8.0+** (ou MariaDB 10.11+) para armazenamento local dos snapshots
  * **Nginx** (servidor web e proxy reverso)
  * **Composer 2.x**
  * **Node.js 20+ e NPM** (para build dos assets)
  * **Git**

---

## 2. Instalação de Pacotes no Ubuntu/Debian

```bash
# Atualize a lista de pacotes
sudo apt update && sudo apt upgrade -y

# Instale dependências básicas
sudo apt install -y git curl unzip nginx mysql-server certbot python3-certbot-nginx

# Adicione repositório PHP (Ondrej Sury) se necessário
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instale PHP e extensões necessárias
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-pgsql php8.3-xml \
    php8.3-curl php8.3-mbstring php8.3-bcmath php8.3-zip php8.3-intl
```

---

## 3. Configuração do Banco de Dados Local (MySQL)

Acesse o MySQL e crie o banco da aplicação e o usuário:

```bash
sudo mysql -u root
```

```sql
CREATE DATABASE monitorafacil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'monitora_user'@'localhost' IDENTIFIED BY 'SUA_SENHA_FORTE_AQUI';
GRANT ALL PRIVILEGES ON monitorafacil.* TO 'monitora_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Conectividade Segura com o PostgreSQL do e-SUS PEC

> [!IMPORTANT]
> A aplicação deve ter acesso ao PostgreSQL da prefeitura **exclusivamente com permissão de leitura (`SELECT`)**. Nunca utilize o superusuário `postgres`.

### 4.1 Criação do Usuário Read-Only no Servidor do e-SUS PEC
No servidor do e-SUS PEC (PostgreSQL), crie um usuário restrito:

```sql
-- Criar usuário de leitura
CREATE USER esus_readonly WITH PASSWORD 'SENHA_DO_ESUS_READONLY';

-- Conceder permissão de conexão
GRANT CONNECT ON DATABASE esus TO esus_readonly;

-- Conceder permissão de leitura nas tabelas públicas do schema
GRANT USAGE ON SCHEMA public TO esus_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO esus_readonly;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO esus_readonly;
```

### 4.2 Restrição de Rede (Firewall / pg_hba.conf)
No arquivo `pg_hba.conf` do servidor e-SUS PEC, autorize **apenas o IP fixo da VPS da aplicação**:

```text
# Conexão remota permitida apenas para o IP da aplicação
host    esus    esus_readonly    IP_DA_SUA_VPS/32    scram-sha-256
```

Se os servidores estiverem em redes distintas sem VPN, configure um túnel SSH reverso ou túnel WireGuard entre a VPS e o servidor do PEC para que o tráfego não trafegue aberto na internet.

---

## 5. Clonagem e Configuração da Aplicação

```bash
# Clone o projeto para o diretório padrão
sudo mkdir -p /var/www/monitorafacil
sudo chown -R $USER:www-data /var/www/monitorafacil
git clone <URL_DO_REPOSITORIO> /var/www/monitorafacil
cd /var/www/monitorafacil

# Copie e preencha as variáveis de ambiente
cp .env.example .env
nano .env
```

### Variáveis Críticas no `.env`:
```env
APP_NAME="Saúde Brasil 360 Monitor"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://monitora.municipio.gov.br

# Banco Primário da Aplicação
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitorafacil
DB_USERNAME=monitora_user
DB_PASSWORD=SUA_SENHA_FORTE_AQUI

# Conexão Secundária de Ingestão (e-SUS PEC da Prefeitura)
ESUS_DB_HOST=192.168.1.100 # ou IP/Host com acesso ao PEC
ESUS_DB_PORT=5432
ESUS_DB_DATABASE=esus
ESUS_DB_USERNAME=esus_readonly
ESUS_DB_PASSWORD=SENHA_DO_ESUS_READONLY
ESUS_DB_SCHEMA=public
ESUS_DB_SSLMODE=prefer

# Caminho absoluto para o XML CNES de equipes homologadas
ESUS_HOMOLOGATED_XML_PATH=/var/www/monitorafacil/storage/app/cnes/homologadas.xml
ESUS_SCHEDULE_TIMEZONE=America/Maceio
```

### Geração de Chave e Execução Inicial
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm ci && npm run build
```

---

## 6. Configuração do Agendador (Crontab do Sistema)

O Laravel possui um agendador integrado (`schedule:run`) que executa o comando `esus:sync-snapshot` todas as noites às **03:00** (conforme registrado em `routes/console.php`).

Edite o crontab do usuário do sistema:

```bash
sudo crontab -u www-data -e
```

Adicione a seguinte linha no final do arquivo:

```cron
* * * * * cd /var/www/monitorafacil && php artisan schedule:run >> /dev/null 2>&1
```

> [!NOTE]
> O comando acima roda a cada minuto apenas para verificar se há alguma tarefa na fila do Laravel; o comando `esus:sync-snapshot` só será acionado na janela programada (03:00) e possui a flag `withoutOverlapping()` para evitar disparos concorrentes.

---

## 7. Configuração do Nginx e HTTPS

Crie o arquivo de configuração do site:

```bash
sudo nano /etc/nginx/sites-available/monitorafacil
```

Conteúdo:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name monitora.municipio.gov.br;
    root /var/www/monitorafacil/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Ative o site e emita o certificado SSL:
```bash
sudo ln -s /etc/nginx/sites-available/monitorafacil /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Emitir certificado SSL gratuito Let's Encrypt
sudo certbot --nginx -d monitora.municipio.gov.br
```

---

## 8. Permissões de Diretórios

Garanta que o servidor web tenha acesso de leitura e escrita aos diretórios de cache e storage:

```bash
sudo chown -R www-data:www-data /var/www/monitorafacil/storage /var/www/monitorafacil/bootstrap/cache
sudo chmod -R 775 /var/www/monitorafacil/storage /var/www/monitorafacil/bootstrap/cache
```

---

## 9. Procedimento de Atualização Contínua (Deploy)

Para aplicar novas versões ou atualizações do código em produção, utilize o script automatizado:

```bash
cd /var/www/monitorafacil
git pull origin main
bash scripts/deploy.sh
```

---

## 10. Teste Manual da Sincronização em Produção

Após configurar as variáveis de ambiente e o arquivo XML do CNES, você pode testar manualmente o motor de ingestão:

```bash
sudo -u www-data php /var/www/monitorafacil/artisan esus:sync-snapshot
```

Para verificar o log de auditoria no banco:
```sql
SELECT id, status, started_at, finished_at, error_message FROM sync_logs ORDER BY id DESC LIMIT 5;
```
