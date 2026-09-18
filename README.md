# Monitora Fácil (Saúde Brasil 360)

> **Plataforma Municipal de Gestão, Monitoramento e Projeção Financeira da Atenção Primária à Saúde (APS)**

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5%20%7C%208.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-v3.8-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![e--SUS PEC](https://img.shields.io/badge/e--SUS_PEC-PostgreSQL-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://sisaps.saude.gov.br/esus/)
[![Testes](https://img.shields.io/badge/Testes-17%20Aprovados-10B981?style=for-the-badge&logo=pest&logoColor=white)](#testes-automatizados)

---

## 📌 Sumário

- [Visão Geral](#-visão-geral)
- [Arquitetura e Princípios Técnicos](#-arquitetura-e-princípios-técnicos)
- [Funcionalidades Principais](#-funcionalidades-principais)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Requisitos do Sistema](#-requisitos-do-sistema)
- [Instalação e Configuração](#-instalação-e-configuração)
- [Comandos Artisan Personalizados](#-comandos-artisan-personalizados)
- [Variáveis de Ambiente (.env)](#-variáveis-de-ambiente-env)
- [Rotinas de Sincronização e Agendamento](#-rotinas-de-sincronização-e-agendamento)
- [Testes Automatizados](#-testes-automatizados)
- [Guia de Deploy (Hostinger + CloudPanel)](deploy.md)
- [Licença](#-licença)

---

## 🎯 Visão Geral

O **Saúde Brasil 360 Monitor** é uma aplicação gerencial desenvolvida para apoiar Secretarias Municipais de Saúde no acompanhamento contínuo dos indicadores da Atenção Primária sob o modelo de cofinanciamento federal da APS (Saúde Brasil 360), alinhado à **Nota Técnica nº 30/2025 - CGESCO/DESCO/SAPS/MS**.

A plataforma opera no modelo **Single-Tenant**, com banco de dados isolado e customização por município (*white-label*), consolidando dados do prontuário eletrônico **e-SUS PEC** e dos arquivos oficiais de homologação do **CNES** sem interferir na operação das unidades básicas de saúde.

---

## 🏛 Arquitetura e Princípios Técnicos

```mermaid
graph TD
    subgraph "Fontes de Dados Oficiais"
        A["e-SUS PEC (PostgreSQL)<br/>Prontuário das Unidades"]
        B["XML CNES Homologado<br/>XmlParaESUS31_*.xml"]
    end

    subgraph "Processamento Noturno (ETL)"
        C["php artisan esus:sync-snapshot<br/>Rotina Agendada (02:00)"]
    end

    subgraph "Armazenamento Municipal"
        D[("MySQL Local<br/>monitorafacil")]
        D1["Snapshots Quadrimestrais<br/>Equipes & Cadastros"]
        D2["Configurações & Usuários<br/>Settings & Users"]
        D --> D1
        D --> D2
    end

    subgraph "Painel do Gestor (Web)"
        E["Interface Livewire 3 + Tailwind v4<br/>Sidebar Executiva"]
    end

    A -- "Leitura Read-Only" --> C
    B -- "Validação IBGE & INEs" --> C
    C -- "Grava Snapshots" --> D
    D1 -- "Consulta Instantânea (< 100ms)" --> E
```

### Princípios Fundamentais

1. **Zero Impacto no e-SUS PEC**: O painel web nunca realiza consultas diretas no PostgreSQL de produção do e-SUS. Todas as páginas consultam apenas o MySQL local, garantindo tempo de resposta instantâneo e eliminando qualquer risco de lentidão nas salas de atendimento médico ou de enfermagem.
2. **Leitura Estritamente Segura (Read-Only)**: O acesso ao banco do e-SUS ocorre por meio de credenciais com permissão exclusiva de leitura (`SELECT`).
3. **Consolidação em Snapshots Quadrimestrais**: Os dados são agregados por ano e competência quadrimestral (1º, 2º e 3º quadrimestre), mantendo o histórico inalterado para auditoria e planejamento.

---

## ✨ Funcionalidades Principais

### 1. 🧭 Sidebar Executiva e Navegação Rápida
- Barra lateral fixa no desktop com tema escuro executivo (`#0c1f1c`) e detalhes em degradê esmeralda.
- Indicador pulsante em tempo real da conexão com a base do e-SUS PEC e fuso horário municipal (`America/Maceio`).
- Atalhos de rolagem suave (`#equipes`, `#cadastros`, `#simulador`).
- Drawer deslizante responsivo com animação e controle via Alpine.js para smartphones e tablets.

### 2. 👥 Estrutura Assistencial (Equipes Homologadas)
- Leitura automatizada do XML de homologação ministerial do CNES.
- Contabilização das equipes ativas categorizadas em:
  - **eSF**: Equipes de Saúde da Família.
  - **eSB**: Equipes de Saúde Bucal (40h, 30h, etc.).
  - **eMulti**: Equipes Multiprofissionais (Ampliadas, Complementares e Estratégicas).
- Totalizador consolidado e tratamento visual diferenciado entre zero homologado e ausência de consolidação.

### 3. 📋 Vínculo e Território (Situação dos Cadastros)
- Monitoramento de cadastros territoriais segundo a regra de atualização nos últimos **24 meses**:
  - **MICI**: Cadastros Individuais (Atualizados vs Desatualizados).
  - **MICDT**: Cadastros Domiciliares e Territoriais / Domicílios (Atualizados vs Desatualizados).
- Exibição de totais absolutos e percentual de cobertura territorial atualizada.

### 4. 🩺 Componente de Qualidade (Nota Técnica 30/2025)
- Acompanhamento detalhado dos indicadores clínicos por faixas de desempenho (*Ótimo*, *Bom*, *Suficiente*, *Regular*):
  - **Saúde da Família (C1 a C7)**: Mais Acesso (C1), Crianças (C2), Gestante e Puérpera (C3), Diabéticos (C4), Hipertensos (C5), Idosos (C6) e Mulheres (C7).
  - **Saúde Bucal (B1 a B6)**: Primeira Consulta Programada (B1), Tratamento Concluído (B2), Taxa de Exodontias (B3), Escovação Supervisionada (B4), Procedimentos Preventivos (B5) e Restauração Atraumática - ART (B6).
  - **e-Multi (M1 e M2)**: Atendimentos por pessoa (M1) e Ações interprofissionais (M2).
- Iconografia executiva contextual dedicada para cada indicador.
- Tratamento compassivo para métricas em processamento com referência padrão `0`.

### 5. 💲 Simulador de Repasses e Planejamento Financeiro
- Ferramenta de estimativa do componente de **Vínculo e Acompanhamento Territorial** de eSF (40h).
- Simulação interativa baseada nas 4 faixas de desempenho da Nota Técnica 30/2025:
  - **Ótimo**: R$ 18.000,00 / mês por equipe.
  - **Bom**: R$ 13.500,00 / mês por equipe.
  - **Suficiente**: R$ 9.000,00 / mês por equipe.
  - **Regular**: R$ 4.500,00 / mês por equipe.
- Botão de atalho rápido *"Todas em Bom"*.
- Cálculo instantâneo do valor bruto mensal e da projeção para as 4 parcelas do quadrimestre.
- Alertas visuais e validação em tempo real para garantir que o número de equipes distribuídas seja exatamente igual ao total homologado.

### 6. 🔐 Acesso e Segurança
- Autenticação restrita ao gestor/administrador municipal.
- Proteção contra força bruta (*Rate Limiting* integrado ao `LoginRequest`).
- Sessões protegidas no banco de dados e hash Bcrypt com fator de custo 12.
- Registro detalhado de execuções de sincronização na tabela `sync_logs`.

---

## 📁 Estrutura do Projeto

```
monitorafacil/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── EsusSyncSnapshot.php      # ETL de consolidação noturna do PEC e XML
│   │       └── ResetAdminPassword.php    # Utilitário CLI para redefinição de senha
│   ├── Enums/
│   │   ├── SyncStatus.php                # Status da sincronização (success, failed, running)
│   │   └── TeamType.php                  # Tipos de equipe (esf, esaude_bucal, emulti)
│   ├── Http/
│   │   ├── Controllers/Auth/             # Autenticação de sessões do gestor
│   │   └── Requests/Auth/LoginRequest.php# Validação e rate limiting de login
│   ├── Livewire/Dashboard/
│   │   ├── FinancialSimulator.php        # Lógica reativa do simulador financeiro
│   │   ├── QualityOverview.php           # Painel de indicadores C1-C7, B1-B6 e M1-M2
│   │   ├── QuarterSelector.php           # Seletor global de ano e quadrimestre
│   │   ├── RegistrationsOverview.php     # Cards de cadastros MICI e MICDT
│   │   └── TeamsOverview.php             # Indicadores de equipes homologadas
│   ├── Models/                           # Modelos Eloquent (User, Setting, Consolidation, etc.)
│   └── Services/                         # Regras de negócio (Cálculos financeiros, Snapshots, Settings)
├── config/
│   ├── bootstrap_admin.php               # Parâmetros padrão do usuário gestor inicial
│   └── esus.php                          # Caminho do XML homologado e fuso horário
├── database/
│   ├── migrations/                       # Esquemas das tabelas locais no MySQL
│   └── seeders/                          # Seeders de configuração inicial e admin
├── importacao/                           # Repositório de arquivos XML CNES municipais
├── lang/
│   └── pt_BR/                            # Localização completa em Português do Brasil
├── resources/
│   ├── css/app.css                       # Design System em Tailwind CSS v4
│   └── views/
│       ├── layouts/app.blade.php         # Layout principal com a Sidebar Executiva
│       ├── livewire/dashboard/           # Componentes Blade reativos do painel
│       └── dashboard.blade.php           # Visão geral do painel municipal
├── routes/
│   ├── web.php                           # Rotas da interface web
│   └── console.php                       # Agendamento de comandos cron
└── tests/                                # Testes automatizados (Unitários e de Funcionalidade)
```

---

## 💻 Stack Tecnológica & Requisitos do Sistema

### Stack em Uso
| Camada | Tecnologia | Versão em Uso |
| :--- | :--- | :--- |
| **Framework Backend** | [Laravel](https://laravel.com) | **v13.32.0** (Laravel 13.x) |
| **Ambiente PHP** | [PHP](https://php.net) | **v8.5.0** (requisito mínimo `>= 8.2`) |
| **Camada Reativa** | [Livewire](https://livewire.laravel.com) | **v3.8.9** |
| **Engine Interativa** | [Alpine.js](https://alpinejs.dev) | **v3.x** |
| **Design & CSS** | [Tailwind CSS](https://tailwindcss.com) | **v4.0.0** |
| **Build & Tooling** | [Vite](https://vitejs.dev) | **v6.2.0** |
| **Banco Local (App)** | [MySQL](https://mysql.com) | **>= 8.0** |
| **Banco e-SUS PEC** | [PostgreSQL](https://www.postgresql.org) | **>= 9.6** (acesso somente leitura) |
| **Gerenciador de Pacotes**| [Composer](https://getcomposer.org) / [NPM](https://npmjs.com) | **Composer >= 2.x** / **Node >= 18.x** |

### Extensões PHP Requeridas
- `pdo_mysql`, `pdo_pgsql`, `xml`, `xmlreader`, `mbstring`, `bcmath`, `curl`, `opcache`.

---

## 🚀 Instalação e Configuração

### 1. Clonar o repositório e acessar a pasta

```bash
git clone <url-do-repositorio> monitorafacil
cd monitorafacil
```

### 2. Instalar as dependências de backend e frontend

```bash
composer install
npm install
```

### 3. Configurar o arquivo `.env`

Copie o arquivo de exemplo:

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` informando as credenciais dos bancos:

```env
# Configurações do Sistema
APP_NAME="Saude Brasil 360 Monitor"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Maceio
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Banco de Dados Local da Aplicação (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitorafacil
DB_USERNAME=root
DB_PASSWORD=

# Conexão com o e-SUS PEC da Prefeitura (PostgreSQL - Leitura)
ESUS_DB_HOST=192.168.2.50
ESUS_DB_PORT=5433
ESUS_DB_DATABASE=esus
ESUS_DB_USERNAME=esus_leitura
ESUS_DB_PASSWORD="sua_senha_leitura"
ESUS_DB_SCHEMA=public
ESUS_DB_SSLMODE=prefer

# Arquivo XML de Homologação CNES e Fuso Horário
ESUS_HOMOLOGATED_XML_PATH="importacao/XmlParaESUS31_270915.xml"
ESUS_SCHEDULE_TIMEZONE=America/Maceio

# Credenciais do Administrador Inicial
SEED_ADMIN_NAME="Administrador"
SEED_ADMIN_EMAIL="admin@monitorafacil.test"
SEED_ADMIN_PASSWORD="SuaSenhaForteAqui"
```

### 4. Executar as migrações e seeders

```bash
php artisan migrate --seed
```

### 5. Compilar os assets do frontend

Para ambiente de desenvolvimento:
```bash
npm run dev
```

Para ambiente de produção:
```bash
npm run build
```

### 6. Iniciar o servidor local

```bash
php artisan serve
```

Acesse no navegador: `http://127.0.0.1:8000`

---

## 🛠 Comandos Artisan Personalizados

### Sincronização e Consolidação do e-SUS PEC

Executa a leitura das tabelas do e-SUS PEC, cruza com as equipes ativas do XML homologado e gera o snapshot do quadrimestre:

```bash
php artisan esus:sync-snapshot
```

### Gerenciamento de Senha do Administrador

Redefine a senha de um administrador existente ou gera uma senha forte de 16 caracteres automaticamente:

```bash
# Definir uma nova senha explicitamente:
php artisan admin:reset-password admin@monitorafacil.test --password="NovaSenhaSegura123!"

# Ou gerar uma senha segura aleatória:
php artisan admin:reset-password
```

---

## ⏰ Rotinas de Sincronização e Agendamento

O comando `esus:sync-snapshot` está configurado em [`routes/console.php`](file:///c:/Users/rayhe/Downloads/monitorafacil/routes/console.php) para rodar automaticamente todas as madrugadas às **02:00**, no fuso horário configurado no município (`America/Maceio`):

```php
Schedule::command('esus:sync-snapshot')
    ->dailyAt('02:00')
    ->timezone(config('esus.schedule_timezone', 'America/Maceio'))
    ->withoutOverlapping()
    ->onOneServer();
```

### Em ambiente de Desenvolvimento:

```bash
php artisan schedule:work
```

### Em ambiente de Produção (Linux / Cron):

Adicione a seguinte entrada ao `crontab -e`:

```cron
* * * * * cd /caminho/para/o/monitorafacil && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Testes Automatizados

A suíte de testes cobre fluxos de autenticação, integridade de snapshots, regras de negócio do simulador financeiro e tratamento de falhas em conexões ou arquivos ausentes.

Para executar todos os testes:

```bash
php artisan test
```

Saída esperada:
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Unit\FinancialProjectionServiceTest
   PASS  Tests\Feature\AuthenticationTest
   PASS  Tests\Feature\EsusSyncSnapshotTest
   PASS  Tests\Feature\ExampleTest

   Tests:    17 passed (59 assertions)
   Duration: ~1.20s
```

---

## 📄 Licença

Software de uso restrito e gestão municipal da Atenção Primária à Saúde. Todos os direitos reservados.
