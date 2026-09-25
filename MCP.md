# Servidores MCP (Model Context Protocol) · Monitora Fácil

Este documento documenta os servidores MCP integrados ao projeto **Monitora Fácil**, explicando suas finalidades, ferramentas disponíveis, quando utilizá-los e como configurá-los em ambientes de desenvolvimento orientados a agentes de IA (como Antigravity IDE e Claude Desktop).

---

## 📌 Sumário

1. [O que é o MCP?](#-o-que-é-o-mcp)
2. [Servidores MCP Disponíveis](#-servidores-mcp-disponíveis)
   - [1. postgres-esus-readonly](#1-postgres-esus-readonly)
   - [2. laravel-artisan-runner](#2-laravel-artisan-runner)
   - [3. esus-file-validator](#3-esus-file-validator)
   - [4. rag-service](#4-rag-service)
3. [Quando Utilizar Cada Servidor](#-quando-utilizar-cada-servidor)
4. [Instruções de Configuração](#-instruções-de-configuração)
5. [Diretrizes de Segurança e Boas Práticas](#-diretrizes-de-segurança-e-boas-práticas)

---

## 🧠 O que é o MCP?

O **Model Context Protocol (MCP)** é um padrão aberto que estabelece uma ponte de comunicação bidirecional e segura entre assistentes de IA e os recursos do sistema (bancos de dados, ferramentas de terminal, parsers de arquivos e APIs internas).

No **Monitora Fácil**, os servidores MCP permitem que o assistente de IA inspecione diretamente o esquema do e-SUS PEC, execute testes automatizados, valide arquivos oficiais de homologação e consulte o acervo regulamentar da APS via RAG local sem comprometer a estabilidade do banco de produção ou alucinar regras de negócio.

---

## 🛠 Servidores MCP Disponíveis

```mermaid
graph LR
    subgraph "Ambiente da IDE / Agente IA"
        Agent["Antigravity / Claude Desktop"]
    end

    subgraph "Servidores MCP (Stdio)"
        MCP1["postgres-esus-readonly<br/>(@modelcontextprotocol/server-postgres)"]
        MCP2["laravel-artisan-runner<br/>(Artisan CLI Runner)"]
        MCP3["esus-file-validator<br/>(Validador XML / CSV)"]
        MCP4["rag-service<br/>(RAG Normativo da APS & Schemas)"]
    end

    subgraph "Alvos no Sistema"
        DW[("PostgreSQL e-SUS PEC<br/>(Somente Leitura)")]
        App["Aplicação Laravel 13<br/>(Testes / Migrações / Schemas)"]
        Files["Arquivos e-SUS<br/>(XML CNES / CSV SIAPS)"]
        Docs["Base Vetorial RAG<br/>(PDFs APS & Schemas)"]
    end

    Agent -- "JSON-RPC (stdio)" --> MCP1
    Agent -- "JSON-RPC (stdio)" --> MCP2
    Agent -- "JSON-RPC (stdio)" --> MCP3
    Agent -- "JSON-RPC (stdio)" --> MCP4

    MCP1 -- "SELECT / EXPLAIN" --> DW
    MCP2 -- "php artisan [cmd]" --> App
    MCP3 -- "Inspeção e Validação" --> Files
    MCP4 -- "Busca Semântica & BM25" --> Docs
```

---

### 1. `postgres-esus-readonly`

Servidor oficial para consultas analíticas somente leitura na base PostgreSQL do e-SUS PEC municipal.

* **Executável**: `npx -y @modelcontextprotocol/server-postgres`
* **Transporte**: `stdio`
* **Ferramenta Principal**:
  * `query`: Executa consultas SQL (`SELECT`, `EXPLAIN`, `SHOW`) diretamente no PostgreSQL do e-SUS.
* **Características**:
  * Modo estritamente **read-only** (sem permissão de escrita ou alteração de dados).
  * Conexão com timeout configurado para evitar travamento em consultas pesadas.

---

### 2. `laravel-artisan-runner`

Servidor dedicado para execução segura e controlada de comandos do Laravel Artisan diretamente a partir da raiz da aplicação.

* **Localização**: `.agents/mcp/artisan-runner/index.js`
* **Executável**: `node .agents/mcp/artisan-runner/index.js`
* **Transporte**: `stdio` (JSON-RPC)
* **Ferramentas Disponíveis**:
  * `artisan_run`: Executa qualquer comando Artisan permitido no projeto (ex: `migrate:status`, `route:list`, `cache:clear`, `view:clear`).
  * `artisan_test`: Dispara a suíte de testes com suporte opcional a filtro de classes/métodos (ex: `filter: "C4IndicatorTest"`).
  * `artisan_inspect_schema`: Executa a rotina `php artisan esus:inspect-schema` em transação somente leitura para gerar o contrato de metadados do e-SUS PEC.

---

### 3. `esus-file-validator`

Servidor utilitário voltado à inspeção de formato, integridade e nós principais de arquivos e-SUS e relatórios de homologação.

* **Localização**: `.agents/mcp/esus-validator/index.js`
* **Executável**: `node .agents/mcp/esus-validator/index.js`
* **Transporte**: `stdio` (JSON-RPC)
* **Ferramentas Disponíveis**:
  * `inspect_csv_headers_and_sample`: Lê o cabeçalho e as primeiras linhas de relatórios CSV do SIAPS ou exportações do e-SUS.
  * `validate_esus_xml_structure`: Inspeciona os nós principais de arquivos XML de homologação ministerial do CNES (`XmlParaESUS31_*.xml`) para validação de tags de equipes e competência.

---

### 4. `rag-service`

Servidor de **Retrieval-Augmented Generation (RAG)** local para recuperação semântica e léxica (Dense Embeddings 384d + BM25) das regras ministeriais da APS e esquemas da base de dados.

* **Localização**: `.agents/mcp/rag-service/index.js`
* **Executável**: `node .agents/mcp/rag-service/index.js`
* **Transporte**: `stdio` (JSON-RPC)
* **Base de Conhecimento Indexada**:
  * Notas Metodológicas C1 a C7 em `importacao/referencia/`
  * NT 08/2026 (CVAT / Avaliação Quadrimestral / Componente II e III)
  * NT 30/2025 (Vínculo e Acompanhamento Territorial)
  * `DATABASE-SCHEMA.md` e migrações em `database/migrations/`
* **Ferramentas Disponíveis**:
  * `search_aps_rules(query: string, indicador?: string, tipo_conteudo?: string, limit?: number)`: Recupera trechos normativos oficiais com isolamento estrito por indicador (`C1` a `C7`, `CVAT` ou `GERAL`) e tipo de conteúdo (`criterio_inclusao`, `criterio_exclusao`, `boas_praticas`, `codigos_cid_ciap`, `profissionais_validos`, `periodo_avaliacao`, `metodologia_calculo`), impedindo contaminação de regras entre indicadores distintos.
  * `search_db_schema(query: string, indicador?: string, limit?: number)`: Recupera definições de tabelas, tipos de dados, índices e relacionamentos internos documentados no `DATABASE-SCHEMA.md` e nas migrations.
* **Script de Ingestão**: Execute `node .agents/rag/ingest.js` para reindexar a base após adicionar novos documentos ou migrações.

---

## 🎯 Quando Utilizar Cada Servidor

| Cenário de Uso | Servidor Recomendado | Ferramenta Indicada |
| :--- | :--- | :--- |
| **Antes de codificar regras de negócio, busca ativa ou notas de C1–C7** | `rag-service` | `search_aps_rules` |
| **Consultar critérios de inclusão/exclusão, prazos (DUM/DPP) ou CIDs/CIAPs oficiais** | `rag-service` | `search_aps_rules` |
| **Verificar colunas, tipos e índices de tabelas locais (C2–C7, CVAT, etc.)** | `rag-service` | `search_db_schema` |
| **Auditar dados reais no e-SUS PEC** sem rodar o ETL completo | `postgres-esus-readonly` | `query` |
| **Inspecionar colunas ou índices do DW PEC** para criação de regras normativas (C1–C7) | `postgres-esus-readonly` | `query` |
| **Rodar suíte de testes automatizados** após alterações de código | `laravel-artisan-runner` | `artisan_test` |
| **Verificar status de migrações ou limpar cache** de visualização | `laravel-artisan-runner` | `artisan_run` |
| **Gerar inventário de metadados do DW PEC** em `storage/app/private/` | `laravel-artisan-runner` | `artisan_inspect_schema` |
| **Checar conformidade do XML ministerial do CNES** antes de importar | `esus-file-validator` | `validate_esus_xml_structure` |
| **Examinar colunas de planilhas CSV do SIAPS** para carga nominal | `esus-file-validator` | `inspect_csv_headers_and_sample` |


---

## ⚙️ Instruções de Configuração

### 1. Configuração Global da IDE (`~/.gemini/config/mcp_config.json`)

Para que a IDE carregue os servidores globalmente com o caminho absoluto correto do workspace no Windows:

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
> **Atenção no Windows**: O campo `cwd` deve usar barras normais (`/`) ou barras invertidas escapadas (`\\`), e deve apontar para a raiz real do projeto para evitar erros do tipo `chdir ${workspaceRoot}: The system cannot find the file specified`.

### 2. Configuração Portável do Repositório (`.agents/mcp_config.json`)

O repositório já inclui a definição portável dos servidores para integração contínua e compartilhamento entre membros da equipe em `.agents/mcp_config.json` e `.antigravity/mcp_config.json`:

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

## 🔒 Diretrizes de Segurança e Boas Práticas

1. **Política Zero Mock (Dados Reais)**:
   - Toda consulta analítica sobre indicadores ou coortes deve ser validada contra os dados reais extraídos do DW e-SUS PEC municipal.
   - Nunca utilizar consultas sintéticas ou alterar registros na base de produção.
2. **PostgreSQL Estritamente Somente Leitura**:
   - O servidor `postgres-esus-readonly` opera sob o usuário `esus_leitura`, impedindo operações destrutivas (`INSERT`, `UPDATE`, `DELETE`, `DROP`).
3. **Resolução Dinâmica de Caminhos**:
   - Os servidores `.agents/mcp/artisan-runner/index.js` e `.agents/mcp/esus-validator/index.js` possuem rotinas automáticas com `fileURLToPath(import.meta.url)` que resolvem os caminhos de arquivos relativos à raiz do projeto, mesmo quando invocados a partir de diretórios de execução externos.
