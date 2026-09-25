# Servidores MCP (Model Context Protocol) & RAG Normativo · Monitora Fácil

Este documento documenta os servidores **Model Context Protocol (MCP)** e a arquitetura de **RAG (Retrieval-Augmented Generation)** integrados ao projeto **Monitora Fácil**, explicando suas finalidades, ferramentas disponíveis, exemplos de uso e diretrizes para ambientes de desenvolvimento assistidos por IA (como Antigravity IDE, Claude Desktop e Cursor).

---

## 📌 Sumário

1. [O que é o MCP?](#-o-que-é-o-mcp)
2. [Arquitetura dos Servidores](#-arquitetura-dos-servidores)
3. [Servidores MCP Disponíveis](#-servidores-mcp-disponíveis)
   - [1. rag-service (RAG Normativo da APS & Schemas)](#1-rag-service)
   - [2. laravel-artisan-runner (Artisan CLI Runner)](#2-laravel-artisan-runner)
   - [3. postgres-esus-readonly (DW e-SUS PEC)](#3-postgres-esus-readonly)
   - [4. esus-file-validator (Validador XML / CSV)](#4-esus-file-validator)
4. [Tabela de Decisão: Quando Utilizar Cada Servidor](#-tabela-de-decisão-quando-utilizar-cada-servidor)
5. [Instruções de Configuração por Ambiente](#-instruções-de-configuração-por-ambiente)
6. [Pipeline de Ingestão e Reindexação RAG](#-pipeline-de-ingestão-e-reindexação-rag)
7. [Diretrizes de Segurança e Boas Práticas](#-diretrizes-de-segurança-e-boas-práticas)

---

## 🧠 O que é o MCP?

O **Model Context Protocol (MCP)** é um protocolo aberto que estabelece uma ponte padronizada, segura e bidirecional entre modelos de IA e os recursos do projeto:
- **Base regulamentar da APS**: Consulta semântica e léxica das regras ministeriais sem alucinação de critérios.
- **Modelagem de dados**: Consulta de tabelas, colunas, tipos e índices do banco local.
- **Banco de produção e-SUS PEC**: Inspeção direta do esquema PostgreSQL em modo somente leitura.
- **Comandos de desenvolvimento**: Execução de rotinas do Laravel Artisan e testes automatizados.
- **Arquivos de homologação**: Validação de XML ministerial do CNES e planilhas do SIAPS.

---

## 🏛 Arquitetura dos Servidores

```mermaid
graph LR
    subgraph "Ambiente da IDE / Agente IA"
        Agent["Antigravity / Claude Desktop / Cursor"]
    end

    subgraph "Servidores MCP (Stdio JSON-RPC)"
        MCP_RAG["rag-service<br/>(Dense 384d + BM25)"]
        MCP_ART["laravel-artisan-runner<br/>(CLI Artisan Runner)"]
        MCP_PG["postgres-esus-readonly<br/>(@modelcontextprotocol/server-postgres)"]
        MCP_VAL["esus-file-validator<br/>(Parser XML CNES & CSV)"]
    end

    subgraph "Alvos no Sistema"
        RAG_STORE[("Base Vetorial Local<br/>knowledge_store.json")]
        APP["Aplicação Laravel 13<br/>(MySQL / Migrations / Tests)"]
        DW[("PostgreSQL e-SUS PEC<br/>(Somente Leitura)")]
        FILES["Arquivos Municipais<br/>(XML CNES / CSV SIAPS)"]
    end

    Agent -- "search_aps_rules / search_db_schema" --> MCP_RAG
    Agent -- "artisan_run / artisan_test" --> MCP_ART
    Agent -- "query (SQL)" --> MCP_PG
    Agent -- "validate / inspect" --> MCP_VAL

    MCP_RAG --> RAG_STORE
    MCP_ART --> APP
    MCP_PG --> DW
    MCP_VAL --> FILES
```

---

## 🛠 Servidores MCP Disponíveis

### 1. `rag-service`

Servidor de **Retrieval-Augmented Generation (RAG)** local para recuperação semântica e léxica híbrida (Dense Embeddings 384d + BM25) das regras ministeriais da APS e da modelagem da base de dados.

* **Localização**: `.agents/mcp/rag-service/index.js`
* **Executável**: `node .agents/mcp/rag-service/index.js`
* **Transporte**: `stdio` (JSON-RPC)
* **Base de Conhecimento Indexada**:
  - Notas Metodológicas C1 a C7 em `importacao/referencia/`
  - NT nº 8/2026 (CVAT / Avaliação Quadrimestral / Componente II e III)
  - NT nº 30/2025 (Vínculo e Acompanhamento Territorial)
  - `DATABASE-SCHEMA.md` e migrations em `database/migrations/`
* **Características**:
  - **Zero Dependência Externa**: Execução 100% local e offline em < 15ms.
  - **Isolamento Estrito por Indicador**: Previne contaminação de regras entre indicadores distintos (ex: regras de gestante C3 nunca vazam para diabetes C4).

#### Ferramentas Disponíveis:

#### `search_aps_rules`
Procura trechos normativos oficiais das Notas Metodológicas e Técnicas da APS.

* **Parâmetros**:
  - `query` (obrigatório, `string`): Pergunta ou termos de busca (ex: `"códigos CIAP CID diabetes ativo"`, `"intervalo mínimo entre visitas ACS"`, `"DUM DPP 42 semanas"`).
  - `indicador` (opcional, `string`): Filtro estrito: `"C1"`, `"C2"`, `"C3"`, `"C4"`, `"C5"`, `"C6"`, `"C7"`, `"CVAT"` ou `"GERAL"`.
  - `tipo_conteudo` (opcional, `string`): Filtro de conteúdo:
    - `"criterio_inclusao"`: Denominador, população elegível, cadastro individual, idade mínima/máxima.
    - `"criterio_exclusao"`: Condições resolvidas, óbito, saída do território, perda de vínculo.
    - `"boas_praticas"`: Quadro 01, práticas A a K, pontuações, pesos e intervalos mínimos.
    - `"codigos_cid_ciap"`: Códigos CIAP-2, CID-10, vacinas e procedimentos SIGTAP válidos.
    - `"profissionais_validos"`: CBOs de médicos, enfermeiros, odontólogos e ACS.
    - `"periodo_avaliacao"`: Janelas de oportunidade (6m, 12m, 24m, 36m, 60m).
    - `"metodologia_calculo"`: Fórmulas, numeradores, notas finais e faixas de qualidade.
  - `limit` (opcional, `number`, default `5`): Quantidade máxima de trechos.

* **Exemplo de Retorno**:
```markdown
### [Resultado 1] Quadro 01. Boas Práticas Clínicas de Diabetes
- **Indicador:** C4 | **Tema:** diabetes | **Tipo:** boas_praticas
- **Fonte:** Nota Metodológica C4 - Cuidado da pessoa com diabetes.pdf (Página 5)
- **Relevância Híbrida:** 88.5% (Vetorial: 77.0% / Léxica: 100.0%)

> (A) Ter realizado pelo menos 01 consulta médica ou de enfermagem nos últimos 6 meses (20 pts).
> (B) Ter realizado pelo menos 01 aferição de Pressão Arterial nos últimos 6 meses (15 pts).
> (C) Ter realizado antropometria com peso e altura simultâneos nos últimos 12 meses (15 pts)...
```

#### `search_db_schema`
Procura definições de tabelas, colunas, tipos de dados, índices e migrações do Monitora Fácil no `DATABASE-SCHEMA.md` e em `database/migrations/`.

* **Parâmetros**:
  - `query` (obrigatório, `string`): Nome da tabela ou termos da modelagem (ex: `"c4_nominal_diabetics"`, `"cvat_team_evaluations"`, `"tabelas de coorte"`).
  - `indicador` (opcional, `string`): Filtrar por indicador associado (`"C1"` a `"C7"`, `"CVAT"`, `"GERAL"`).
  - `limit` (opcional, `number`, default `5`): Quantidade de resultados.

---

### 2. `laravel-artisan-runner`

Servidor dedicado para execução controlada e segura de comandos do Laravel Artisan diretamente na raiz da aplicação.

* **Localização**: `.agents/mcp/artisan-runner/index.js`
* **Executável**: `node .agents/mcp/artisan-runner/index.js`
* **Transporte**: `stdio` (JSON-RPC)

#### Ferramentas Disponíveis:
- `artisan_run`: Executa comandos permitidos (ex: `migrate:status`, `route:list`, `cache:clear`, `config:clear`, `esus:process-data --scope=c4 --year=2026 --quarter=3`).
- `artisan_test`: Executa a suíte de testes do PHPUnit com suporte a filtros (ex: `filter: "C4IndicatorTest"`, `filter: "AuthenticationTest"`).
- `artisan_inspect_schema`: Gera contrato de metadados do PostgreSQL em `storage/app/private/` em modo somente leitura.

---

### 3. `postgres-esus-readonly`

Servidor oficial para consultas analíticas somente leitura na base PostgreSQL do e-SUS PEC municipal.

* **Executável**: `npx -y @modelcontextprotocol/server-postgres <string_conexao>`
* **Transporte**: `stdio`

#### Ferramenta Principal:
- `query`: Executa consultas SQL (`SELECT`, `EXPLAIN`) diretamente no PostgreSQL do e-SUS PEC para auditar fatos clínicos reais (`tb_fat_atendimento_individual`, `tb_fat_vacinacao`, `tb_acomp_cidadaos_vinculados`, etc.).

---

### 4. `esus-file-validator`

Servidor utilitário para inspeção e validação de arquivos ministeriais e relatórios de homologação.

* **Localização**: `.agents/mcp/esus-validator/index.js`
* **Executável**: `node .agents/mcp/esus-validator/index.js`
* **Transporte**: `stdio` (JSON-RPC)

#### Ferramentas Disponíveis:
- `inspect_csv_headers_and_sample`: Lê cabeçalho e linhas amostrais de relatórios CSV do SIAPS ou exportações do e-SUS PEC.
- `validate_esus_xml_structure`: Inspeciona nós principais de arquivos XML ministerial do CNES (`XmlParaESUS31_*.xml`).

---

## 🎯 Tabela de Decisão: Quando Utilizar Cada Servidor

| Cenário de Uso | Servidor Recomendado | Ferramenta Indicada |
| :--- | :--- | :--- |
| **Antes de codificar regras de negócio, busca ativa ou notas de C1–C7** | `rag-service` | `search_aps_rules` |
| **Consultar critérios de inclusão/exclusão, prazos (DUM/DPP) ou CIDs/CIAPs oficiais** | `rag-service` | `search_aps_rules` |
| **Verificar colunas, tipos e índices de tabelas locais (C2–C7, CVAT, etc.)** | `rag-service` | `search_db_schema` |
| **Auditar dados reais no e-SUS PEC** sem rodar o ETL completo | `postgres-esus-readonly` | `query` |
| **Inspecionar colunas ou índices do DW PEC** para criação de regras normativas (C1–C7) | `postgres-esus-readonly` | `query` |
| **Rodar suíte de testes automatizados** após alterações de código | `laravel-artisan-runner` | `artisan_test` |
| **Executar processamento de dados do DW PEC por escopo (C1 a C7)** | `laravel-artisan-runner` | `artisan_run` |
| **Verificar status de migrações ou limpar cache** de visualização | `laravel-artisan-runner` | `artisan_run` |
| **Gerar inventário de metadados do DW PEC** em `storage/app/private/` | `laravel-artisan-runner` | `artisan_inspect_schema` |
| **Checar conformidade do XML ministerial do CNES** antes de importar | `esus-file-validator` | `validate_esus_xml_structure` |
| **Examinar colunas de planilhas CSV do SIAPS** para carga nominal | `esus-file-validator` | `inspect_csv_headers_and_sample` |

---

## ⚙️ Instruções de Configuração por Ambiente

### 1. Configuração Global da IDE (`~/.gemini/config/mcp_config.json`)

Para que a IDE carregue os 4 servidores globalmente com os caminhos corretos no Windows:

```json
{
  "mcpServers": {
    "postgres-esus-readonly": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-postgres",
        "postgresql://esus_leitura:XD3ucg758UGl%7BQ%5DeYsoS%3FCY%5Bp0C%402@192.168.2.50:5433/esus"
      ]
    },
    "laravel-artisan-runner": {
      "command": "node",
      "args": [
        ".agents/mcp/artisan-runner/index.js"
      ],
      "cwd": "C:/Users/rayhe/Downloads/monitorafacil"
    },
    "esus-file-validator": {
      "command": "node",
      "args": [
        ".agents/mcp/esus-validator/index.js"
      ],
      "cwd": "C:/Users/rayhe/Downloads/monitorafacil"
    },
    "rag-service": {
      "command": "node",
      "args": [
        ".agents/mcp/rag-service/index.js"
      ],
      "cwd": "C:/Users/rayhe/Downloads/monitorafacil"
    }
  }
}
```

> [!IMPORTANT]
> **Atenção no Windows**: O campo `cwd` deve usar barras normais (`/`) ou barras invertidas escapadas (`\\`), apontando para a raiz real do projeto para evitar erros do tipo `chdir ${workspaceRoot}: The system cannot find the file specified`.

### 2. Configuração Portável do Repositório (`.agents/mcp_config.json`)

O repositório inclui a definição portável compartilhada em `.agents/mcp_config.json` e `.antigravity/mcp_config.json`:

```json
{
  "mcpServers": {
    "postgres-esus-readonly": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-postgres",
        "postgresql://esus_leitura:XD3ucg758UGl%7BQ%5DeYsoS%3FCY%5Bp0C%402@192.168.2.50:5433/esus"
      ]
    },
    "laravel-artisan-runner": {
      "command": "node",
      "args": [
        ".agents/mcp/artisan-runner/index.js"
      ]
    },
    "esus-file-validator": {
      "command": "node",
      "args": [
        ".agents/mcp/esus-validator/index.js"
      ]
    },
    "rag-service": {
      "command": "node",
      "args": [
        ".agents/mcp/rag-service/index.js"
      ]
    }
  }
}
```

---

## 🔄 Pipeline de Ingestão e Reindexação RAG

Sempre que novas Notas Metodológicas forem adicionadas em `importacao/referencia/`, ou quando novas migrações e alterações forem feitas no `DATABASE-SCHEMA.md`, a base de conhecimento RAG deve ser reindexada:

```bash
node .agents/rag/ingest.js
```

### O que o script de ingestão executa:
1. Executa `.agents/rag/extract_pdfs.py` para extrair texto de todos os PDFs regulamentares;
2. Segmenta o conteúdo em chunks semânticos (500 a 800 tokens) preservando cabeçalhos e metadados;
3. Indexa as seções e tabelas ativas do `DATABASE-SCHEMA.md`;
4. Indexa o código DDL das migrações em `database/migrations/`;
5. Gera vetores densos (384 dimensões) e índice invertido BM25;
6. Grava a base consolidada em `.agents/rag/storage/knowledge_store.json`.

---

## 🔒 Diretrizes de Segurança e Boas Práticas

1. **Consulta Prévia Obrigatória ao RAG (`search_aps_rules`)**:
   - Conforme a **Regra 5** do [`AGENTS.md`](AGENTS.md), antes de implementar ou refatorar serviços de busca ativa (`C*ActiveSearchService`), cálculo de práticas (`C*PracticeCalculator`) ou snapshots (`C*SnapshotService`), o assistente de IA deve consultar a tool `search_aps_rules`.
2. **Política Zero Mock (Dados Reais)**:
   - Toda consulta analítica sobre indicadores ou coortes deve ser validada contra dados reais extraídos do DW e-SUS PEC municipal. Nunca utilizar dados simulados ou fictícios.
3. **PostgreSQL Estritamente Somente Leitura**:
   - O servidor `postgres-esus-readonly` opera sob o usuário `esus_leitura`, impedindo operações destrutivas (`INSERT`, `UPDATE`, `DELETE`, `DROP`).
4. **Resolução Dinâmica de Caminhos**:
   - Os servidores MCP em Node.js utilizam detecção dinâmica da raiz do projeto (`artisan` presente no diretório corrente ou caminho relativo ao módulo), garantindo portabilidade entre diretórios de trabalho.
