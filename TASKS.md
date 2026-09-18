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
  - [x] Ajustar e validar caminho relativo do arquivo XML de homologação ministerial CNES em `importacao/` (`ESUS_HOMOLOGATED_XML_PATH="importacao/XmlParaESUS31_270915.xml"`) com suporte nativo a caminhos relativos (`base_path`).
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

- [x] **Fase 10: Controle de Versão Remoto e Repositório Público no GitHub**
  - [x] Conectar com a conta GitHub oficial `rayhenrique`.
  - [x] Criar repositório público `rayhenrique/monitorafacil`.
  - [x] Vincular remote `origin` via HTTPS e configurar rastreamento da branch `main`.
  - [x] Realizar push inicial de todo o código, documentação e suíte de testes.

- [x] **Fase 11: Documentação de Deploy na VPS Hostinger com CloudPanel**
  - [x] Criar guia prático `deploy.md` calibrado para o domínio `monitorafacil.kltecnologia.com`.
  - [x] Mapear credenciais, paths do CloudPanel (`htdocs/.../public`), usuário SSH `kltecnologia-monitorafacil` e banco MySQL.
  - [x] Documentar emissão de SSL Let's Encrypt e agendamento de Cron Job no CloudPanel.

- [x] **Fase 12: Simplificação da Sidebar e Módulo Visão Geral**
  - [x] Manter exclusivamente o item "Visão Geral" na navegação principal da sidebar desktop e mobile.
  - [x] Remover âncoras secundárias mantendo o painel limpo e modular.

- [x] **Fase 13: Módulo Configurações com Submódulos Completos**
  - [x] Implementar menu expansível *accordion* "Configurações" na Sidebar (Desktop e Mobile).
  - [x] Criar componente de cabeçalho com abas horizontais de navegação cruzada (`x-settings-tabs`).
  - [x] **Submódulo 1 - Usuários** (`/configuracoes/usuarios`): CRUD completo de operadores com busca, paginação, modais de cadastro/edição, hash de senha e proteção contra autoexclusão.
  - [x] **Submódulo 2 - Município** (`/configuracoes/municipio`): Edição institucional de Nome, Código IBGE, CNES da Sede e upload de Logotipo oficial com prévia imediata.
  - [x] **Submódulo 3 - Log de Auditoria** (`/configuracoes/logs-auditoria`): Histórico paginado das execuções de sincronização (`sync_logs`) com filtros por status (Sucesso/Falha/Execução), tempo de duração e modal com detalhes do erro.
  - [x] **Submódulo 4 - Conexão e-SUS** (`/configuracoes/conexao-esus`): Exibição dos parâmetros PostgreSQL (senhas mascaradas) e botão interativo para teste de conectividade em tempo real com diagnóstico de tabelas PEC (`tb_equipe`, `tb_cidadao`, etc.) e aferição de latência (ms).
  - [x] **Submódulo 5 - Processar Dados** (`/configuracoes/processar-dados`): Disparo manual da rotina de consolidação (`esus:sync-snapshot`) com feedback ao vivo e cards de resumo do último snapshot de equipes e cadastros (MICI e MICDT).
  - [x] **Submódulo 6 - Importar CNES / XML** (`/configuracoes/importar-cnes-xml`): Upload de arquivos XML homologados pelo Ministério da Saúde, validação de integridade sem DTD com `XMLReader`, verificação do código IBGE, prévia detalhada das equipes (eSF, eSB, eMulti) e salvamento em `importacao/`.
  - [x] Suíte de testes automatizados completa com 26 testes aprovados (104 asserções).

- [ ] **Fase 14: Revisão do indicador C2 pelo DW PEC e notas técnicas**
  - [x] Substituir o percentual simulado por extração de coorte mensal e boas práticas A–E documentadas no DW.
  - [x] Calcular a média quadrimestral apenas dos meses com crianças que completaram dois anos e aplicar peso 2.
  - [x] Impedir geração ou exibição de C2 fictício; sinalizar ausência de resultado e origem preliminar do DW.
  - [x] Cobrir regras de pontuação, exceção eAP, meses sem coorte e falha de conexão com testes locais.
  - [x] Separar a coorte dos quatro meses das crianças que já completaram dois anos no quadrimestre em andamento.
  - [x] Exibir prévia de pontuação para todas as crianças dos meses M1–M4 com registros disponíveis até a extração, inclusive meses futuros, identificando que não é nota oficial.
  - [ ] Validar o esquema e a execução somente de leitura no PEC acessível pela VPS.
  - [ ] Comparar amostra de resultados por equipe/mês com o Siaps e investigar diferenças por RNDS, ações coletivas e vínculo histórico.
