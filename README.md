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
- [Servidores MCP (Model Context Protocol)](#-servidores-mcp-model-context-protocol)
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
- Ausência de resultado C2 exibida como **sem dados**, sem converter falta de coorte em zero.

#### C1 · Mais Acesso pelo DW PEC

O C1 lê somente snapshots produzidos pela rotina CLI. O numerador usa os tipos `1` e `2`; o denominador usa os tipos `1`, `2`, `4`, `5` e `6`. A população elegível fica restrita às equipes eSF/eAP, aos sete CBOs definidos na nota metodológica e aos atendimentos com CNS do profissional, data de nascimento e CPF ou CNS válido do cidadão.

Durante o quadrimestre, o painel mostra uma prévia com as competências já monitoradas. Meses futuros ou ausentes aparecem como **sem dados**, sem serem convertidos em zero. Depois das quatro competências, a média simples de M1 a M4 produz a estimativa quadrimestral local. O resultado oficial continua sendo o publicado pelo Siaps.

Uma falha de schema ou leitura interrompe o C1 e preserva o último snapshot válido. Snapshots antigos sem a versão de cálculo atual ficam ocultos. A confirmação do nome do cidadão ainda depende da validação de uma fonte estável no esquema disponível na VPS, pois esse campo não está presente na tabela fato documentada publicamente.

#### C2 · estimativa local do DW PEC

O processamento C2 faz somente consultas de leitura no PostgreSQL do PEC e grava agregados no MySQL local; a tela do indicador consulta apenas esses snapshots. A coorte do quadrimestre inclui todas as crianças vinculadas a eSF/eAP que completam dois anos entre o primeiro e o último dia dos quatro meses. Todas recebem uma prévia calculada com as práticas registradas até a data da extração, inclusive as dos meses futuros M1–M4; a tela informa separadamente quantas já completaram dois anos. Cada prática A–E vale 20 pontos. A média quadrimestral local usa os meses com crianças na coorte, inclusive futuros. Essa antecipação é uma ferramenta de acompanhamento e não é a nota oficial prevista na Nota Técnica nº 8/2026.

O cálculo lê vínculos atuais, atendimentos de puericultura, antropometria, visitas ACS/TACS e vacinação documentados no DW. É **estimativa preliminar**, pois o DW local pode não conter vacinas da RNDS, ações coletivas e o histórico de vínculo usado pelo Siaps. Não use o percentual local como resultado oficial de cofinanciamento. O C2 antigo calculado por simulação fica oculto até uma nova extração bem-sucedida.

Na VPS com acesso ao PEC, após instalar esta versão, execute `php artisan migrate --force` e `php artisan esus:process-data --scope=c2 --year=2026 --quarter=3`; confira a contagem completa da coorte e compare uma amostra de equipes e meses com o Siaps. A aplicação não envia fichas, XML ou Thrift para o PEC.

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

### Inventário somente leitura do DW PEC

Antes de criar ou atualizar o espelho analítico no MySQL, gere o contrato real do esquema acessível pela VPS:

```bash
php artisan esus:inspect-schema
```

O comando abre uma transação PostgreSQL explicitamente somente leitura e grava em `storage/app/private/` um JSON com tabelas, colunas, tipos, índices e estimativas do catálogo. Nenhuma linha clínica ou identificação de cidadão é extraída. Um caminho alternativo pode ser informado com `--output=arquivo.json`.

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

O comando `esus:sync-snapshot` está configurado em `routes/console.php` para rodar automaticamente às **03:00** e `esus:process-data --scope=all` às **03:30**, no fuso horário configurado no município (`America/Maceio`). O escopo geral cobre C1, C2 e C3; a relação nominal CVAT é extraída separadamente.

```php
Schedule::command('esus:sync-snapshot')
    ->dailyAt('03:00')
    ->timezone(config('esus.schedule_timezone'))
    ->withoutOverlapping();
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

O botão **CVAT > Agendar extração** em *Configurações > Processar Dados* envia um job para a fila `database`; ele não consulta o PEC durante a requisição web. No servidor, mantenha um worker ativo. Confira o usuário do serviço, o diretório e o caminho do PHP 8.5 no modelo `scripts/monitorafacil-queue.service.example` antes de instalá-lo:

```bash
sudo cp scripts/monitorafacil-queue.service.example /etc/systemd/system/monitorafacil-queue.service
sudo systemctl daemon-reload
sudo systemctl enable --now monitorafacil-queue.service
sudo systemctl status monitorafacil-queue.service
```

Use `QUEUE_CONNECTION=database` e `DB_QUEUE_RETRY_AFTER=1200` no `.env`; o prazo de reentrega precisa superar os 900 segundos permitidos ao job. O `scripts/deploy.sh` executa a extração CVAT após as migrações, sinaliza os workers para reinício e avisa se o serviço não estiver ativo. A migração e a extração precisam alcançar o PEC a partir do servidor.

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

## 🤖 Servidores MCP (Model Context Protocol)

O projeto conta com integração ao padrão **Model Context Protocol (MCP)**, permitindo que agentes de IA inspecionem a base do e-SUS PEC, rodem rotinas Artisan e validem arquivos em ambientes de desenvolvimento assistido:

* **`postgres-esus-readonly`**: Consultas analíticas estritamente somente leitura no DW PostgreSQL do e-SUS PEC.
* **`laravel-artisan-runner`**: Execução controlada de comandos Artisan (`test`, `esus:inspect-schema`, `migrate:status`, etc.).
* **`esus-file-validator`**: Validação de arquivos XML de homologação do CNES e leitura de cabeçalhos de relatórios CSV do SIAPS.

👉 Para guia detalhado de ferramentas, políticas de segurança e instruções de configuração, consulte o arquivo [**MCP.md**](MCP.md).

---

## 📄 Licença

Software de uso restrito e gestão municipal da Atenção Primária à Saúde. Todos os direitos reservados.
