# Servidores MCP (Model Context Protocol) · Monitora Fácil

Este documento documenta os servidores MCP integrados ao projeto **Monitora Fácil**, explicando suas finalidades, ferramentas disponíveis, quando utilizá-los e como configurá-los em ambientes de desenvolvimento orientados a agentes de IA (como Antigravity IDE e Claude Desktop).

---

## 📌 Sumário

1. [O que é o MCP?](#-o-que-é-o-mcp)
2. [Servidores MCP Disponíveis](#-servidores-mcp-disponíveis)
   - [1. postgres-esus-readonly](#1-postgres-esus-readonly)
   - [2. laravel-artisan-runner](#2-laravel-artisan-runner)
   - [3. esus-file-validator](#3-esus-file-validator)
3. [Quando Utilizar Cada Servidor](#-quando-utilizar-cada-servidor)
4. [Instruções de Configuração](#-instruções-de-configuração)
5. [Diretrizes de Segurança e Boas Práticas](#-diretrizes-de-segurança-e-boas-práticas)

---

## 🧠 O que é o MCP?

O **Model Context Protocol (MCP)** é um padrão aberto que estabelece uma ponte de comunicação bidirecional e segura entre assistentes de IA e os recursos do sistema (bancos de dados, ferramentas de terminal, parsers de arquivos e APIs internas).

No **Monitora Fácil**, os servidores MCP permitem que o assistente de IA inspecione diretamente o esquema do e-SUS PEC, execute testes automatizados e valide arquivos oficiais de homologação (CNES/SIAPS) sem comprometer a estabilidade do banco de produção ou exigir comandos manuais constantes no terminal.

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
    end

    subgraph "Alvos no Sistema"
        DW[("PostgreSQL e-SUS PEC<br/>(Somente Leitura)")]
        App["Aplicação Laravel 13<br/>(Testes / Migrações / Schemas)"]
        Files["Arquivos e-SUS<br/>(XML CNES / CSV SIAPS)"]
    end

    Agent -- "JSON-RPC (stdio)" --> MCP1
    Agent -- "JSON-RPC (stdio)" --> MCP2
    Agent -- "JSON-RPC (stdio)" --> MCP3

    MCP1 -- "SELECT / EXPLAIN" --> DW
    MCP2 -- "php artisan [cmd]" --> App
    MCP3 -- "Inspeção e Validação" --> Files
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

## 🎯 Quando Utilizar Cada Servidor

| Cenário de Uso | Servidor Recomendado | Ferramenta Indicada |
| :--- | :--- | :--- |
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
