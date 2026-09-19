# 🚀 Guia de Deploy — VPS Hostinger com CloudPanel

Este guia fornece o passo a passo prático para colocar o **Monitora Fácil** em produção na sua VPS Hostinger gerenciada pelo **CloudPanel**, utilizando as configurações exatas do seu ambiente.

---

## 📋 Informações do Seu Ambiente (CloudPanel)

| Item | Valor Configurado |
| :--- | :--- |
| **Domínio da Aplicação** | `monitorafacil.kltecnologia.com` |
| **IP da VPS (Hostinger)** | `72.60.142.2` |
| **Usuário SSH do Site** | `kltecnologia-monitorafacil` |
| **Diretório da Aplicação** | `/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com` |
| **Diretório Raiz (Document Root)** | `/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com/public` |
| **Versão do PHP** | `PHP 8.5` (Template: Laravel 13) |
| **Banco de Dados MySQL** | `monitorafacil` |
| **Usuário do Banco MySQL** | `monitorafacil` |
| **Repositório GitHub** | `https://github.com/rayhenrique/monitorafacil.git` |

---

## Passo 1: Apontamento de DNS

No painel onde seu domínio `kltecnologia.com` está gerenciado (Hostinger DNS, Cloudflare ou Registro.br):

1. Crie uma entrada de **Tipo A**:
   - **Nome / Host**: `monitorafacil`
   - **Tipo**: `A`
   - **Valor / Destino**: `72.60.142.2`
   - **TTL**: 300 (ou Automático)
2. Se usar **Cloudflare**, deixe a nuvem em modo *DNS Only* (cinza) temporariamente até emitir o SSL pelo CloudPanel.

---

## Passo 2: Confirmar o Site no CloudPanel

Conforme suas capturas de tela:
1. **Inscrição (Template)**: `Laravel 13`
2. **Nome do domínio**: `monitorafacil.kltecnologia.com`
3. **Versão do PHP**: `PHP 8.5`
4. **Usuário do site**: `kltecnologia-monitorafacil`
5. Na aba **Definições**, certifique-se de que o **Diretório raiz** aponta para:
   ```text
   monitorafacil.kltecnologia.com/public
   ```

---

## Passo 3: Criar o Banco de Dados no CloudPanel

Na aba **Bancos de dados** do site `monitorafacil.kltecnologia.com`:
1. Clique em **Adicionar banco de dados**.
2. **Nome do banco de dados**: `monitorafacil`
3. **Nome de usuário do banco**: `monitorafacil`
4. **Senha do usuário do banco**: Gere uma senha forte e **anote-a** para colocar no `.env`.
5. Clique em **Salvar**.

---

## Passo 4: Acessar a VPS via SSH

Abra seu terminal local (PowerShell, Terminal ou Git Bash) e conecte com o usuário do site:

```bash
ssh kltecnologia-monitorafacil@72.60.142.2
```

*(Se preferir conectar como `root`, você pode usar `ssh root@72.60.142.2` e depois rodar `su - kltecnologia-monitorafacil`)*.

---

## Passo 5: Clonar o Código do GitHub

Navegue até a pasta `htdocs` do seu usuário:

```bash
cd /home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com
```

Se a pasta contiver arquivos padrão criados pelo CloudPanel, limpe-os antes de clonar:

```bash
# Limpar arquivos temporários do CloudPanel
rm -rf * .[!.]*

# Clonar o repositório público diretamente na pasta atual
git clone https://github.com/rayhenrique/monitorafacil.git .
```

---

## Passo 6: Instalar Dependências e Compilar Assets

### 6.1 Instalar dependências PHP (Composer)
```bash
composer install --no-dev --optimize-autoloader
```

### 6.2 Instalar dependências JavaScript e compilar os assets (Vite + Tailwind v4)
```bash
npm install
npm run build
```

> **Dica**: Se o comando `npm` não estiver disponível no usuário do site, instale o Node.js no servidor como `root`:
> ```bash
> sudo apt update && sudo apt install -y nodejs npm
> ```

---

## Passo 7: Configurar o Arquivo `.env` de Produção

Crie o `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Edite o arquivo `.env`:

```bash
nano .env
```

Ajuste as linhas principais:

```env
APP_NAME="Monitora Fácil"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://monitorafacil.kltecnologia.com
APP_TIMEZONE=America/Maceio
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Conexão MySQL Local (CloudPanel)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitorafacil
DB_USERNAME=monitorafacil
DB_PASSWORD="SUA_SENHA_GERADA_NO_CLOUDPANEL"

# Conexão com o e-SUS PEC Municipal (PostgreSQL - Somente Leitura)
ESUS_DB_HOST=IP_DO_SERVIDOR_ESUS
ESUS_DB_PORT=5433
ESUS_DB_DATABASE=esus
ESUS_DB_USERNAME=esus_leitura
ESUS_DB_PASSWORD="sua_senha_leitura"
ESUS_DB_SCHEMA=public
ESUS_DB_SSLMODE=prefer

# Arquivo XML de Homologação CNES e Fuso Horário (Caminho Relativo)
ESUS_HOMOLOGATED_XML_PATH="importacao/XmlParaESUS31_270915.xml"
ESUS_SCHEDULE_TIMEZONE=America/Maceio

# Administrador Inicial para Seed
SEED_ADMIN_NAME="Administrador"
SEED_ADMIN_EMAIL="admin@monitorafacil.test"
SEED_ADMIN_PASSWORD="SuaSenhaForteAqui"
```

Gere a chave da aplicação e crie o link simbólico do storage:

```bash
php8.5 artisan key:generate
php8.5 artisan storage:link
```

---

## Passo 8: Rodar as Migrações e Criar o Administrador

Execute as migrações e o seeder inicial do banco:

```bash
php8.5 artisan migrate --seed --force
```

Para definir a senha do administrador de forma personalizada ou gerar uma nova:

```bash
php8.5 artisan admin:reset-password admin@monitorafacil.test --password="NovaSenhaSegura2026!"
```

---

## Passo 9: Permissões de Pastas

Garanta que as pastas de cache, logs e storage tenham permissões corretas de escrita:

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Passo 10: Otimizar o Laravel para Alta Performance

Execute os comandos de cache para carregar configurações, rotas e views instantaneamente:

```bash
php8.5 artisan config:cache
php8.5 artisan route:cache
php8.5 artisan view:cache
php8.5 artisan event:cache
```

---

## Passo 11: Ativar Certificado SSL (HTTPS Gratuito) no CloudPanel

1. No CloudPanel, acesse o site `monitorafacil.kltecnologia.com`.
2. Clique na aba **SSL/TLS**.
3. Clique em **Novo Certificado SSL**.
4. Selecione **Let's Encrypt**.
5. Clique em **Criar e Instalar**.
6. O CloudPanel ativará HTTPS automaticamente e renovará o certificado a cada 90 dias.

---

## Passo 12: Configurar o Cron Job (Agendador Noturno) no CloudPanel

O Monitora Fácil executa a consolidação diária do e-SUS às **02:00**. Para que o agendador do Laravel funcione:

1. No CloudPanel, acesse o site `monitorafacil.kltecnologia.com`.
2. Vá até a aba **Cron Jobs**.
3. Clique em **Adicionar Cron Job**.
4. Configure:
   - **Template / Frequência**: `A cada minuto (* * * * *)`
   - **Comando**:
     ```bash
     php8.5 /home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com/artisan schedule:run >> /dev/null 2>&1
     ```
5. Clique em **Salvar**.

---

## 🔄 Como Atualizar a Aplicação no Futuro (Deploy Contínuo)

Sempre que fizer novas alterações e enviar para o GitHub (`git push`), basta executar o script de deploy automatizado no servidor:

```bash
cd /home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com
./deploy.sh
```

*(Ou executar manualmente os comandos equivalentes contidos no script):*

```bash
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
```

---

## 🛠 Extensão PostgreSQL (e-SUS PEC) no Servidor

Caso a sincronização com o banco e-SUS PEC apresente erro de driver não encontrado, verifique se a extensão `php8.5-pgsql` está instalada na VPS como `root`:

```bash
sudo apt update && sudo apt install -y php8.5-pgsql
sudo systemctl restart php8.5-fpm
```

---

### Pronto!
Sua aplicação estará disponível em segurança e com alta velocidade em:
👉 **`https://monitorafacil.kltecnologia.com`**
