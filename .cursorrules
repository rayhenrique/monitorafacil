# Laravel 13.x + Livewire 3 Context & Rules

## Core Stack
- PHP 8.2+
- Laravel 13.x
- MySQL 8.0 (Banco da Aplicação)
- PostgreSQL (Apenas Leitura via driver secundário `pgsql_esus`)
- Livewire 3 + Alpine.js + Tailwind CSS

## Naming Conventions & Code Style
- Siga estritamente a PSR-12.
- Estrutura Single-Tenant. Não crie lógicas de multi-tenancy.
- Models: Sempre declare `$fillable`, `casts` para tipagem forte, e defina relacionamentos com tipagem de retorno.
- Migrations: Tipagem forte, índices em colunas frequentemente pesquisadas (como `year` e `quarter`).
- Variáveis/Métodos: `camelCase`. Tabelas/Colunas: `snake_case`.

## Architecture & Integration
- NENHUMA QUERY NO POSTGRESQL DEVE ACONTECER DURANTE REQUISIÇÕES WEB.
- O painel/Dashboard lê estritamente do banco primário (`mysql`). 
- Apenas a rotina de Command (rodando em background/CLI) deve invocar `DB::connection('pgsql_esus')`.
- Evite colocar lógica de negócios pesada diretamente dentro dos componentes Livewire; delegue extrações complexas para Actions ou Services.

## Workflow de Tarefas
1. Leia o `TASKS.md` antes de iniciar a escrita de código.
2. Siga a ordem exata das fases estabelecidas.
3. Ao finalizar uma etapa, altere o respectivo `[ ]` para `[x]` no arquivo `TASKS.md`.

## Regras de Design e Criação de Módulos
- **NUNCA COPIAR DESIGN EXTERNO**: Em nenhuma hipótese copie o design, paleta de cores, estilos visuais ou componentes de páginas ou sistemas externos (como DashSaúde ou outros).
- **PRESERVAÇÃO DO DESIGN SYSTEM MONITORA FÁCIL**: Mantenha estritamente a identidade própria do Monitora Fácil (sidebar executiva escura `#0c1f1c`, acentos esmeralda `#0f766e`/`#10b981`, canvas claro `#f5f7f6` e badges `MF`).
- **INSPIRAÇÃO EXCLUSIVA EM MÓDULOS**: Referências externas servem apenas para identificar quais módulos e conceitos gerenciais de APS são pertinentes.
- **DESENVOLVIMENTO COMPASSIVO**: Daqui em diante, novos módulos serão desenvolvidos de forma compassiva, gradual e planejada passo a passo.