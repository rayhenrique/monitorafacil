# Diretrizes do Projeto Monitora Fácil

## Regras de Design e Arquitetura

### 1. Política de Rodapé Único (NUNCA DUPLICAR O RODAPÉ)
- O único rodapé permitido em todo o sistema é o rodapé global renderizado no layout principal em `resources/views/layouts/app.blade.php`.
- **NUNCA** insira rodapés institucionais, de créditos (como "PWDEV_") ou de versão dentro de páginas, views filhas ou componentes Livewire/Blade.
- O rodapé global do layout exibe:
  - Lado esquerdo: `Dados consolidados para apoio à gestão municipal da APS.`
  - Lado direito: `Monitora Fácil · {Município} · {v1.x.x}` (com link para novidades da versão).
- Onde for identificada duplicidade de rodapé em qualquer tela, remova o rodapé interno imediatamente.

### 2. Originalidade do Design e Identidade Visual
- Nunca copiar cores, gradientes ou estilos visuais de sistemas externos.
- Preservar o Design System próprio do Monitora Fácil:
  - Sidebar executiva escura `#0c1f1c`;
  - Acentos esmeralda `#0f766e` / `#10b981` / `#16a34a`;
  - Canvas claro `#f5f7f6`;
  - Badges e monogramas `MF`.

### 3. Integridade de Dados
- Utilizar exclusivamente dados 100% reais extraídos e consolidados a partir do DW / e-SUS PEC municipal.
- Nunca utilizar dados mockados, fictícios ou simulados.

### 4. Manutenção Contínua da Documentação do Banco de Dados (DATABASE-SCHEMA.md)
- **Sempre que atualizar algo do banco de dados (criação/alteração de tabelas, migrations, colunas ou índices), atualize obrigatoriamente o arquivo `DATABASE-SCHEMA.md`**.
- O `DATABASE-SCHEMA.md` deve refletir fielmente tanto as tabelas e colunas ativas (Seção 2) quanto o alinhamento com a arquitetura geral da aplicação.

### 5. Consulta Obrigatória ao RAG Normativo da APS (MCP rag-service)
- **Sempre que for implementar ou refatorar serviços de busca ativa (`C*ActiveSearchService`), cálculo de práticas (`C*PracticeCalculator`), snapshots (`C*SnapshotService`) ou regras do CVAT (`CvatEvaluationService`), deves primeiro invocar a tool `search_aps_rules` para obter os critérios oficiais de inclusão/exclusão e códigos CID/CIAP válidos**.
- Para consultar colunas, tipos e relacionamentos de tabelas existentes antes de criar migrations ou queries, utilize a tool `search_db_schema`.
- O RAG local é gerado pelo script `.agents/rag/ingest.js` e indexa todos os documentos regulamentares em `importacao/referencia/`, o `DATABASE-SCHEMA.md` e as migrations em `database/migrations/`.

---

## Skills Especializadas do Projeto (`.agents/skills/`)

Todos os agentes, subagentes e o Codex devem ativar e seguir rigorosamente as skills especializadas disponíveis no diretório `.agents/skills/` conforme o escopo da tarefa:

### 1. `new-indicator-calculator` (`.agents/skills/new-indicator-calculator/SKILL.md`)
- **Propósito:** Scaffold padronizado e geração completa da cadeia de arquivos para novos indicadores de saúde da APS (C1 a C7 ou novas regras do Ministério da Saúde).
- **Quando Acionar Automaticamente:**
  - Sempre que for criar um novo indicador clínico ou refatorar profundamente um indicador existente.
  - Ao estruturar a cadeia de 6 camadas: Migrations & Models (`C*CohortSnapshot`, `C*Nominal*`), Extração DW (`C*DwService`), Calculador de Práticas Puras (`C*PracticeCalculator`), Busca Ativa (`C*ActiveSearchService`), Persistência de Snapshots (`C*SnapshotService`) e Testes automatizados.
  - Garante tipagem estrita (`declare(strict_types=1);`), lotes (`CHUNK_SIZE`) e atualização do `DATABASE-SCHEMA.md`.

### 2. `audit-sql-dw` (`.agents/skills/audit-sql-dw/SKILL.md`)
- **Propósito:** Auditoria estrita de performance e otimização de consultas SQL executadas sobre a réplica volumosa do PostgreSQL do e-SUS PEC.
- **Quando Acionar Automaticamente:**
  - Ao escrever ou modificar queries em `C*DwService.php`, `FamilyHealthService.php` ou `CvatNominalDwService.php`.
  - Ao investigar lentidão, timeouts ou estouro de memória no processamento de dados.
  - Exige a aplicação das 4 regras de ouro: uso de `WHERE EXISTS` em vez de `JOIN` 1:N massivo, filtro de tempo obrigatório com `tb_dim_tempo`, eliminação de funções em colunas no `WHERE` (prevenção de Seq Scan) e chunking controlado com conexão `READ ONLY`.

### 3. `livewire-screen-generator` (`.agents/skills/livewire-screen-generator/SKILL.md`)
- **Propósito:** Criação e refatoração de telas e fluxos em Livewire v3 em total conformidade com `UX-CONTRACT.md` e `DESIGN.md`.
- **Quando Acionar Automaticamente:**
  - Ao criar novas páginas, abas ou componentes Livewire (`app/Livewire/`) e views Blade (`resources/views/livewire/`).
  - Ao implementar filtros dinâmicos (com debounce de 300ms), paginação com `WithPagination` (Tailwind) e modais de diálogo.
  - Ao integrar feedback visual em tarefas demoradas via componente `<x-processing-progress>`.
  - **Obrigatório:** Fiscalizar e garantir a **Política de Rodapé Único**, impedindo qualquer rodapé interno dentro de componentes ou views filhas.

### 4. `test-coverage-validator` (`.agents/skills/test-coverage-validator/SKILL.md`)
- **Propósito:** Validação de integridade de testes via PHPUnit após alterações de código, cobrindo casos de borda metodológicos da APS.
- **Quando Acionar Automaticamente:**
  - Após qualquer modificação em serviços, calculadores, extrações ou telas, antes de submeter commits ou aprovar tarefas.
  - Executa testes via MCP `laravel-artisan-runner` (`artisan_test` com filtro de classe/método).
  - Garante a presença dos 5 casos de borda obrigatórios: cidadão sem CPF (só com CNS), CBO não homologada, eventos fora do quadrimestre, duplicidade de cadastro entre equipes e denominador zero.



