# Plano de Execução Sequencial - Laravel 13.x

- [x] **Fase 1: Setup do Projeto e Banco de Dados**
  - [x] Criar novo projeto Laravel 13.x.
  - [x] Instalar e configurar Livewire 3 e Tailwind CSS.
  - [x] Configurar conexão primária `mysql` e conexão secundária `pgsql_esus` no arquivo `config/database.php` (usando variáveis do `.env`).
  - [x] Criar Migrations estritas para `settings`, `consolidation_teams`, `consolidation_registrations` e `sync_logs`.
  - [x] Criar Seeders para popular um usuário admin padrão e as `settings` iniciais.

- [x] **Fase 2: Modelagem e Acesso a Dados**
  - [x] Criar Models (User, Setting, ConsolidationTeam, ConsolidationRegistration, SyncLog) com `$fillable` e `casts` mapeados corretamente.
  - [x] Criar repositório ou service class (ex: `SettingsService`) para acessar configurações do cache de forma eficiente.

- [x] **Fase 3: Motor de Ingestão (ETL via Command)**
  - [x] Criar Console Command `php artisan esus:sync-snapshot`.
  - [x] Implementar a lógica no Command para abrir a conexão `pgsql_esus`, rodar as queries brutas de contagem (Equipes e Cadastros).
  - [x] Gravar os totais no MySQL local usando `updateOrCreate` baseado no Ano/Quadrimestre atual.
  - [x] Registrar o início, fim e possíveis erros na tabela `sync_logs`.
  - [x] Adicionar o Command no `routes/console.php` para rodar diariamente às 02:00 com fuso horário municipal (`America/Maceio`).

- [x] **Fase 4: Painel do Gestor (Dashboard UI)**
  - [x] Configurar Autenticação básica (Laravel Breeze simplificado).
  - [x] Criar layout base Blade com Tailwind CSS, integrando os dados dinâmicos da tabela `settings` (Nome do Município, Logo).
  - [x] Criar componente Livewire `Dashboard\QuarterSelector` para gerenciar estado global de Quadrimestre/Ano selecionado.
  - [x] Criar componente Livewire `Dashboard\TeamsOverview` para renderizar os cards de Equipes.
  - [x] Criar componente Livewire `Dashboard\RegistrationsOverview` para renderizar as estatísticas de vínculo.
  - [x] Criar componente Livewire `Dashboard\FinancialSimulator` com as regras de repasse baseadas nos dados em tela.

- [x] **Fase 5: Testes e Preparações de Deploy**
  - [x] Escrever testes para garantir o funcionamento isolado do Command de Sincronização usando mocks do DB.
  - [x] Preparar script/instruções para setup do cronjob no servidor VPS (ex: integração com crontab padrão).

- [x] **Fase 6: Homologação CNES e Gerenciamento de Credenciais**
  - [x] Ajustar e validar caminho do arquivo XML de homologação ministerial CNES em `importacao/` (`ESUS_HOMOLOGATED_XML_PATH`).
  - [x] Configurar fuso horário oficial municipal `America/Maceio` (`ESUS_SCHEDULE_TIMEZONE`).
  - [x] Gerar e sincronizar nova senha de administrador no banco de dados e no `.env`.
  - [x] Criar comando Artisan `admin:reset-password` para redefinição manual ou geração aleatória segura de senhas via CLI.

- [x] **Fase 7: Sidebar Executiva e Identidade Visual Monitora Fácil**
  - [x] Substituir a barra superior (*top bar*) por uma **Sidebar Executiva** fixa no desktop com tema escuro (`#0c1f1c`).
  - [x] Incluir indicador com pulso luminoso de status da conexão e-SUS PEC e fuso horário.
  - [x] Criar gaveta lateral móvel (*slide-over drawer*) responsiva com suporte nativo a Alpine.js.
  - [x] Adicionar navegação suave por âncoras (`#equipes`, `#cadastros`, `#qualidade`, `#simulador`).
  - [x] Criar barra de contexto superior no desktop com migalhas de pão (*breadcrumbs*) e data atual.
  - [x] Atualizar marca, monograma e badges de `SB` para **`MF`** (**Monitora Fácil**).

- [x] **Fase 8: Internacionalização Completa (PT-BR) e Documentação**
  - [x] Configurar `APP_LOCALE=pt_BR`, `APP_FALLBACK_LOCALE=pt_BR`, `APP_FAKER_LOCALE=pt_BR` e `APP_TIMEZONE=America/Maceio`.
  - [x] Criar pacote completo de traduções em `lang/pt_BR/` (`auth.php`, `pagination.php`, `passwords.php`, `validation.php` e `pt_BR.json`).
  - [x] Reformular totalmente o `README.md` com arquitetura, diagramas Mermaid, módulos, guia de instalação e referências.
  - [x] Registrar diretrizes de preservação de design e desenvolvimento de módulos compassivos em `DESIGN.md`, `.cursorrules` e `cursorrules.md`.

- [x] **Fase 9: Expansão Modular dos Indicadores da Atenção Primária**
  - [x] Reestruturar a seção de Equipes Homologadas com ícones temáticos e descrições detalhadas (eSF, eSB, eMulti).
  - [x] Expandir Vínculo e Acompanhamento:
    - [x] Card de Classificação do Quadrimestre com faixas (Ótimo, Bom, Suficiente, Regular).
    - [x] Card de Total MICI atualizados com percentual, competência/mês, total geral e desatualizados.
    - [x] Card de Total MICI + MICDT atualizados (fichas/domicílios) com percentual e subtotais.
  - [x] Criar novo componente Livewire `QualityOverview` com os indicadores da Nota Técnica 30/2025:
    - [x] Saúde da Família: Indicadores C1 a C7 (Mais Acesso, Crianças, Gestante/Puérpera, Diabéticos, Hipertensos, Idosos, Mulheres).
    - [x] Saúde Bucal: Indicadores B1 a B6 (Primeira Consulta, Tratamento Concluído, Exodontias, Escovação Supervisionada, Procedimentos Preventivos, Restauração ART).
    - [x] e-Multi: Indicadores M1 e M2 (Atendimentos por pessoa, Ações interprofissionais) com suporte a faixas de desempenho.
    - [x] Ícones temáticos executivos dedicados integrados nos cards de todos os indicadores clínicos (C1 a C7, B1 a B6, M1 a M2).
    - [x] Atribuição de valor 0 para dados ainda não consolidados no banco de dados.
  - [x] Adicionar testes automatizados cobrindo o componente de qualidade expandido (totalizando 16 testes aprovados e 57 asserções).

