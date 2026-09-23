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

- [ ] **Fase 15: Revisão normativa do indicador C1 pelo DW PEC**
  - [x] Restringir o numerador aos tipos 1/2 e o denominador aos tipos 1/2/4/5/6 do domínio oficial.
  - [x] Aplicar somente os sete CBOs oficiais, sem filtro por prefixo nem fallback sem CBO.
  - [x] Exigir CNS do profissional, data de nascimento e CPF ou CNS válido do cidadão.
  - [x] Impedir baseline fictício, ocultar snapshots antigos e preservar o último resultado em falha de leitura.
  - [x] Exibir competências ausentes como sem dados e identificar prévias com menos de quatro meses.
  - [x] Cobrir a consulta estrita e a ausência de resultado com testes automatizados locais.
  - [ ] Confirmar na VPS a disponibilidade e o conteúdo das colunas usadas pela consulta, incluindo uma fonte estável para o nome do cidadão.
  - [ ] Comparar amostra por equipe e competência com o resultado preliminar publicado no Siaps.

- [ ] **Fase 16: Camada analítica local do DW PEC no MySQL**
  - [x] Separar referências, entidades canônicas, eventos clínicos e produtos analíticos no desenho do banco.
  - [x] Mapear a lista funcional para tabelas normalizadas, evitando cópias concorrentes de cidadãos, PSE e resultados.
  - [x] Mapear os conjuntos mínimos de dados exigidos pelas notas metodológicas C1 a C7.
  - [x] Documentar limites de fidelidade entre DW local, Siaps, SCNES e RNDS.
  - [x] Implementar e testar o comando `esus:inspect-schema` para inventário de metadados em transação somente leitura.
  - [ ] Executar inventário somente leitura do esquema e da versão do PEC na VPS.
  - [ ] Fechar o dicionário origem-coluna-destino com amostras e contagens da VPS.
  - [ ] Definir política de retenção, criptografia e perfis para dados nominais de saúde.
  - [ ] Implementar controle de lotes, checkpoints, rejeições e validação do contrato de origem.
  - [ ] Implementar referências, unidades, equipes, cidadãos, unificação, vínculos, famílias e condições.
  - [ ] Implementar eventos de cadastro, atendimento, odontologia, procedimentos, atividade coletiva, consumo alimentar, visita e vacinação.
  - [ ] Implementar coortes e evidências por boa prática para C1 a C7.
  - [ ] Implementar produtos de vínculo, Saúde Bucal, e-Multi, vigilância e vacinação infantil.
  - [ ] Validar desempenho, idempotência, correções tardias e preservação do último lote válido.
  - [ ] Comparar amostras por equipe e período com Siaps antes de classificar qualquer resultado como homologado.

- [x] **Fase 17: Refinamento do Módulo C2 - Busca Ativa & Boas Práticas Infantis**
  - [x] Banner de dados gerais com Mês (`2026 / M9`), 5 Boas Práticas Clínicas (A a E) com percentuais e Denominador oficial (`1.016`).
  - [x] Barra de filtros rápidos imediatos (CNS, CPF, Nome, CNES, INE) e seletor de paginação.
  - [x] Personalização de colunas visíveis por checkbox (`Colunas visíveis: X itens selecionados ⌄`).
  - [x] Lista nominal da coorte com máscara LGPD para CNS/CPF e cópia instantânea em 1 clique.
  - [x] Modal de Busca Avançada completo com filtros territoriais, faixas etárias (chips) e toggles booleanos SIM/NÃO.
  - [x] Modal de prontuário e auditoria clínica detalhada das cinco práticas para cada criança.
  - [x] Testes de integração cobrindo o fluxo completo da Busca Ativa C2.

- [x] **Fase 18: Conexão Nominal Real do C2 ao DW e-SUS PEC (v1.14.0)**
  - [x] Tabela `c2_nominal_children` no MySQL para armazenamento atômico e persistência da coorte nominal com proteção de chave única (`year`, `quarter`, `pec_child_id`).
  - [x] Extração de colunas nominais reais (`no_cidadao`, `no_mae_cidadao`, `nu_cpf_cidadao`, `nu_cns_cidadao`, `nu_micro_area`, `nu_cnes_vinc_unidade`, `no_unidade_vinc`, `ds_raca_cor_cidadao`) de `tb_acomp_cidadaos_vinculados` no `C2DwService`.
  - [x] Persistência chunked em lotes de 100 registros durante a extração pelo `C2SnapshotService`.
  - [x] Consulta, filtros e paginação direta sobre a tabela real no `C2ActiveSearchService` com fallback seguro apenas quando a tabela estiver vazia e o PEC indisponível.
  - [x] Processamento e sincronização centralizados no módulo oficial de Processamento de Dados (`/configuracoes/processamento-dados`).
  - [x] Badges no topo identificando origem: "Base Real e-SUS PEC" vs "Demonstração".
  - [x] Cobertura completa de testes automatizados unitários e de feature.
  - [x] **Patch v1.14.1**: Detecção dinâmica via `information_schema` das colunas de `tb_acomp_cidadaos_vinculados`, eliminando `no_mae_cidadao` e prevenindo erro `SQLSTATE[42703]`.
  - [x] **Patch v1.14.2**: Remoção do botão isolado "Sincronizar PEC" da aba C2 e centralização unificada no módulo de Processamento de Dados com botão dedicado "Processar C2 & Lista Nominal".

- [x] **Fase 19: Refinamento de Busca Avançada C2 & Coorte de 7 Quadrimestres (v1.15.0)**
  - [x] Remoção dos campos mockados "Distrito" e "Unidade" na Busca Avançada do C2, preservando apenas "Equipe" (com lista real de equipes eSF homologadas) e "Microárea".
  - [x] Dropdown customizado de seleção de Mês com busca em tempo real, botão de limpeza (`X`), indicador de seta e formatação `MM / YYYY`.
  - [x] Dropdown de Opção de Mês com opções "Mês Selecionado e Próximos Meses" e "Apenas Mês Selecionado".
  - [x] Filtro de Idade (meses) com chips rápidos (`0-6 meses`, `7-12 meses`, `13-24 meses`) e menu multiselect retrátil com seleção geral ("Selecionar todos"), busca e seleção de meses de 0 a 24.
  - [x] Expansão do processamento no e-SUS PEC para 7 quadrimestres (atual + 6 futuros), cobrindo crianças de 0 a 24 meses de idade para funcionamento pleno do filtro etário.
  - [x] Testes automatizados abrangentes cobrindo novos filtros, seletores e coorte de 7 quadrimestres.

- [x] **Fase 20: Módulo C3 - Cuidado na Gestação e Puerpério na APS (v1.16.0)**
  - [x] Modelagem de dados: Migrations e Models `c3_cohort_snapshots` e `c3_nominal_pregnancies`.
  - [x] Motor DW e-SUS PEC (`C3DwService`): Extração com catálogo adaptativo do PostgreSQL, cálculo das 11 boas práticas (A=10 pts, B a K=9 pts cada), cálculo de DUM, DPP, desfecho e fim do 42º dia do puerpério.
  - [x] Persistência em lote (`C3SnapshotService`): Snapshots quadrimestrais e mensais (M1 a M4) para consolidado municipal e individualizado por equipe (INE).
  - [x] Serviço de Busca Ativa (`C3ActiveSearchService`): Suporte a 22 colunas customizáveis, ordenação, paginação, filtros avançados e cálculo de KPIs do banner.
  - [x] Regras de Negócio e Componente III (`FamilyHealthService`): Peso 2.0 (até 2,00 pts), tabela de conceitos da NT 08/2026 e regra de equidade para eAP (tipo 76 - práticas E e J garantidas).
  - [x] Integração Centralizada no Processamento de Dados (`EsusDataProcessingService`, `DataProcessing`): Escopo `--scope=c3` e botão "Processar C3 & Lista Gestantes".
  - [x] Frontend Livewire (`IndicatorDetail`): Cabeçalho C3, decomposição das 11 práticas, evolução M1 a M4, desempenho por equipe, tabela interativa com seletor de colunas, modal de busca avançada e modal de auditoria clínica.
  - [x] Testes de Feature completos (`tests/Feature/FamilyHealthC3Test.php`) com 100% de aprovação.
  - [x] **Patch v1.16.1**: Detecção dinâmica (`information_schema`) de todas as tabelas/colunas que variam entre versões do DW PEC: `tb_dim_cid`/`tb_dim_cid10`, `nu_idade_gestacional`/`nu_idade_gestacional_semanas`, `dt_ultima_menstruacao`/`co_dim_tempo_dum`, `nu_pressao_sistolica`/`nu_medicao_pressao_sistolica`. Corrige `SQLSTATE[42P01]` e `SQLSTATE[42703]` em produção.

- [x] **Fase 21: Revisão normativa e de desempenho do C3 (v1.17.0)**
  - [x] Remoção da consulta correlacionada e do filtro histórico ilimitado responsáveis por `SQLSTATE[57014]`; leitura em lotes de 100 cidadãos e intervalo máximo DUM + 294 + 42 dias.
  - [x] Correção da dimensão da DUM para `tb_dim_tempo_dum`, sem criação de DUM fictícia quando DUM e idade gestacional estão ausentes.
  - [x] Aplicação das listas de CIAP/CID de inclusão e exclusão da Nota Metodológica, resolvidas nas dimensões e filtradas por chave.
  - [x] Coorte limitada ao quadrimestre em que ocorre o 42º dia do puerpério, com prévia mantida para os quadrimestres futuros.
  - [x] Práticas A–K recalculadas em janelas clínicas: visita após primeira consulta, dTpa após 20 semanas, exames SIGTAP reais nos 1º/3º trimestres e consulta/visita puerperal até 42 dias.
  - [x] Eventos do MIP incorporados à pressão arterial, exames laboratoriais e saúde bucal quando individualizados no DW.
  - [x] MICI/MICDT deixam de ser marcados como atualizados sem evidência no DW e o distrito fictício foi removido.
  - [x] Busca ativa C3 alinhada às colunas reais da tabela e dados simulados bloqueados no ambiente de produção.
  - [x] Testes unitários de pontuação, janelas clínicas, exceção eAP e regressão da consulta PostgreSQL.
  - [x] **Patch v1.17.1**: resolução adaptativa de `co_dim_tempo_dum` para `tb_dim_tempo_dum` nas versões atuais ou `tb_dim_tempo` nas instalações legadas do DW PEC.

- [x] **Fase 22: Progresso visível do processamento (v1.18.0)**
  - [x] Barra de progresso estimado exibida imediatamente nos escopos C1, C2, C3 e Geral Completo.
  - [x] Mensagens de etapa durante a conexão, validação, consolidação e finalização, com limite de 92% até a confirmação do servidor.
  - [x] Estado final em 100%, distinguindo conclusão com sucesso ou pendências e liberando os controles.
  - [x] Semântica acessível de `progressbar`, texto equivalente e movimento reduzido.
  - [x] Validação funcional em navegador, viewport desktop e responsividade em 390 × 844 px.

- [x] **Fase 23: Dados reais C1–C3 no dashboard inicial (v1.19.0)**
  - [x] Substituição dos totais estáticos em zero pela distribuição dos snapshots quadrimestrais por equipe.
  - [x] Contagem de Ótimo, Bom, Suficiente e Regular somente para registros com INE e versões de cálculo vigentes.
  - [x] Exclusão do consolidado municipal da contagem para evitar duplicidade.
  - [x] Estado explícito de ausência quando C1, C2 ou C3 ainda não possui consolidação válida.
  - [x] C4 a C7 permanecem identificados como ainda não processados.
  - [x] Testes automatizados cobrem snapshots atuais, versão antiga e registro municipal.

- [x] **Fase 24: Módulo Vínculo e Acompanhamento Territorial - CVAT (v1.20.0)**
  - [x] Posicionamento do novo módulo "Vínculo e Acompanhamento" na navegação lateral (desktop e mobile) antes de Saúde da Família.
  - [x] Migrations e Models para distribuições dimensionais (`cvat_dimension_distributions`) e avaliações individuais das equipes (`cvat_team_evaluations`).
  - [x] Serviço `CvatService` e comando Artisan `php artisan cvat:import-siaps` com leitura e carga idempotente dos arquivos CSV do Siaps.
  - [x] Carga dos 3 últimos quadrimestres oficiais (Q2/2025, Q3/2025, Q1/2026) e aviso explícito da pendência do Q2/2026.
  - [x] Reprodução fiel dos gráficos de barras empilhadas do Siaps para a Dimensão Cadastro (Peso 3) e Dimensão Acompanhamento (Peso 7).
  - [x] Tabela de desempenho das 19 equipes eSF com notas, ordenação, busca, filtros de classificação e totalizador municipal.
  - [x] Enquadramento no Quadro 5 da NT 08/2026 (média municipal de 8,77 em Q1/26 com classificação Ótimo e 100% de incentivo financeiro).
  - [x] Integração dos números oficiais de vínculo no dashboard inicial com link direto para o módulo.
  - [x] Testes automatizados cobrindo rotas, serviço CVAT e componente Livewire.

- [x] **Fase 25: Submódulo Relação Nominal e Busca Ativa no PEC (v1.21.0)**
  - [x] Criação de tabelas e models `cvat_nominal_metrics` e `cvat_nominal_citizens` com campos cadastrais completos, auditoria e LGPD.
  - [x] Serviço `CvatNominalDwService` para extração dos dados do DW PEC e motor de sincronização com persistência e fallback gracioso.
  - [x] Comando Artisan `php artisan cvat:sync-nominal` e integração no fluxo de deploy contínuo (`scripts/deploy.sh` e `deploy.sh`).
  - [x] Painel da Dimensão Cadastro com totalizadores de MICI (36.951), atualizados (36.751), desatualizados (200), sem MICDT (2.047), com MICDT (34.904) e vinculados (35.401).
  - [x] Painel retrátil da Dimensão Acompanhamento com matriz 4×3 cruzando critérios de vulnerabilidade e acompanhamento (Sem Critério, Idoso/Criança, BPC/PBF, Idoso/Criança + BPC/PBF).
  - [x] Barra de busca ativa e filtros instantâneos (CNS, CPF, Nome Cidadão, CNS Profissional, Nome Profissional, CNES, INE, Raça/Cor e paginação de 10 a 100).
  - [x] Tabela nominal de alta densidade com 18 colunas, ordenação, máscaras LGPD com toggle visual e cópia para área de transferência.
  - [x] Modais interativos de Busca Avançada multicritério e Prontuário de Vínculo com ficha completa do cidadão.
  - [x] Navegação integrada no sidebar desktop e gaveta mobile, abas do módulo CVAT e rota `/vinculo-e-acompanhamento/relacao-nominal`.
  - [x] Testes automatizados de rota, renderização, filtros e paginação da Relação Nominal.

- [x] **Fase 26: Processamento Exclusivo e Importação Opcional do Siaps (v1.21.1)**
  - [x] Criação do botão exclusivo "Processar Vínculo e Acompanhamento" no painel de *Configurações > Processar Dados* com sincronização sob demanda do DW PEC.
  - [x] Remoção da importação automática e obrigatória do Siaps do script de deploy (`scripts/deploy.sh` e `deploy.sh`), tornando-a opcional.
  - [x] Criação do modal de upload de arquivos CSV do Siaps diretamente no módulo *Vínculo e Acompanhamento* com detecção automática do formato (Equipes vs Dimensões) e restauração dos arquivos locais do servidor.
  - [x] Testes automatizados para o novo escopo `cvat` em `DataProcessing` e para o modal de upload de CSV em `TerritorialBondingOverview`.

- [x] **Fase 27: Extração Real Completa do DW e-SUS PEC (v1.22.0)**
  - [x] Motor de extração massiva em lote via conexão oficial `pgsql_esus` com cursor seek $O(1)$ (`co_fat_cidadao_pec > $lastId`) na visão `tb_acomp_cidadaos_vinculados`.
  - [x] Cruzamento em lote de datas de cadastro individual (`tb_fat_cad_individual`), domiciliar (`tb_fat_cad_domiciliar`), visitas de ACS (`tb_fat_visita_domiciliar`) e atendimentos (`tb_fat_atendimento_individual`).
  - [x] Cálculo e consolidação matemática das métricas oficiais das Dimensões Cadastro e Acompanhamento sem dados estáticos/amostrais na extração oficial.
  - [x] Comando `php artisan cvat:sync-nominal` e botão "Processar Vínculo e Acompanhamento" com relatório e progresso em tempo real.
  - [x] Diagnóstico resiliente de conectividade com fallback explicativo para execução fora da rede do PEC.

- [x] **Fase 28: Janela de 12 Meses no Acompanhamento Territorial (v1.22.1)**
  - [x] Ajuste da regra de negócio para a Dimensão Acompanhamento: janela de 12 meses (365 dias) contados regressivamente do último dia do quadrimestre avaliado (30/04, 31/08 ou 31/12).
  - [x] Filtragem temporal no PostgreSQL limitando registros até a data de encerramento do período quadrimestral.
  - [x] Cálculo de idade dos cidadãos com base na data final do quadrimestre para correta classificação etária.

- [x] **Fase 29: Regra Estrita de 24 Meses para MICI e MICDT (v1.22.2)**
  - [x] Implementação da regra estrita: MICI e MICDT só são classificados como desatualizados se tiverem mais de 24 meses da data de corte final do quadrimestre avaliado (`mici_date < $cutoffMici`).
  - [x] Qualquer atualização realizada em menos de 24 meses classifica o cadastro como atualizado (`mici_updated = true`, `micdt_updated = true`).
  - [x] Uso de `subMonthsNoOverflow(24)` para eliminar discrepâncias em meses de 31 dias.

- [x] **Fase 30: Redesign Responsivo do Painel de Processamento de Dados (v1.22.3)**
  - [x] Eliminação do estreitamento/compressão do bloco de texto e da barra horizontal com overflow.
  - [x] Reorganização do cabeçalho em largura total (100%) com badges oficiais de cofinanciamento e status PostgreSQL.
  - [x] Grid adaptativo com 5 cards de ação temáticos (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5`) para Vínculo & Território (CVAT), Mais Acesso (C1), Crianças (C2), Gestantes (C3) e Processamento Geral.
  - [x] Micro-interações táteis, divisórias sutis, tags de escopo e feedback de carregamento (`wire:loading`) em cada card.
  - [x] Inclusão de `processCvat` nas diretivas `wire:target` da barra de progresso Livewire.
  - [x] Compilação de assets de produção com Vite (`npm run build`).

- [x] **Fase 31: Relação Nominal como Submódulo Principal do CVAT (v1.22.4)**
  - [x] Remoção definitiva do item e aba "Painel Oficial CVAT" na sidebar (desktop e mobile) e em `territorial-bonding-tabs`.
  - [x] Promoção da Relação Nominal a primeiro submódulo e tela inicial padrão de Vínculo e Acompanhamento.
  - [x] Redirecionamento da raiz do módulo (`/vinculo-e-acompanhamento`) diretamente para `/vinculo-e-acompanhamento/relacao-nominal`.
  - [x] Atualização dos atalhos do dashboard para apontar diretamente para a listagem nominal.
  - [x] Atualização dos testes automatizados de redirecionamento e das dimensões.

- [x] **Fase 32: Alinhamento Integral à Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS & Portaria SAPS nº 161/2024 (v1.23.0)**
  - [x] Definição oficial de Pessoa Acompanhada (Item 2.6.4): $\ge 2$ contatos assistenciais em 12 meses, com obrigatoriedade de $\ge 1$ contato ser Prática de Cuidado (atendimento clínico individual médico/enfermeiro, odontologia, visita do ACS ou atividade coletiva).
  - [x] Ajuste do critério etário de criança para vulnerabilidade: até 5 anos incompletos (4 anos, 11 meses e 29 dias / `$age < 5`), revogando o critério anterior de $< 6$ anos.
  - [x] Exclusões cadastrais na Dimensão Cadastro: desconsideração de cadastros com "Fora de Área (FA)" ou "Mudou-se" e cadastros rápidos simplificados (fator 0).
  - [x] Apuração matemática dos Índices Ponderados X (fatores 0,75 e 1,50) e Y (fatores 1,0; 1,2; 1,3 e 2,5) sobre a população alvo de 47.500 pessoas (19 eSF $\times$ 2.500).
  - [x] Painel Executivo com Escore Final (0 a 10,00 pts), Conceito Ministerial (Ótimo, Bom, Suficiente, Regular) e faixas de repasse do incentivo financeiro federal.
  - [x] Caderno Metodológico (aba guide) integralmente reformulado como réplica didática da Portaria SAPS/MS nº 161/2024 e NT 30/2025, incluindo bônus de satisfação do Meu SUS Digital e regras de desempate de vínculos.
  - [x] Atualização de testes automatizados e versionamento da plataforma.

- [x] **Fase 33: Monitoramento de Equipes (Mensal) com Dados Reais e Remoção de Abas Obsoletas (v1.23.1)**
  - [x] Remoção definitiva das abas obsoletas `cadastro` e `acompanhamento` no menu lateral, componente de abas e rotas, mantendo apenas Relação Nominal, Equipes (Mensal) e Caderno Metodológico.
  - [x] Redirecionamento transparente de acessos legados com `?aba=cadastro` ou `?aba=acompanhamento` para a tela de monitoramento de equipes (`?aba=teams`).
  - [x] Painel superior de síntese com totais reais do município: Mês (2026/M9), Total Ótimo (10 - 52.63%), Total Bom (7 - 36.84%), Total Suficiente (1 - 5.26%) e Total Regular (1 - 5.26%).
  - [x] Tabela de monitoramento de equipes com as 14 colunas completas reproduzindo a exibição de dados da especificação: CNES, Unidade, INE, Equipe, Tipo ESF, Parâmetro (2500), Cadastros Vinculados (35.401 munícipes totais), % C.Vinc/Param, Resultado Cadastro, Score X (3.00), Resultado Acompanhamento, Score Y (7.00), Score Final e Classificação Final.
  - [x] Barra de filtros com busca instantânea por CNES, Unidade, INE, Equipe, Classificação Final e paginação.
  - [x] Modal interativo de Busca Avançada para filtragem por faixas de escores.
  - [x] Nova migration adicionando colunas métricas na tabela `cvat_team_evaluations`.
  - [x] Compilação de assets com Vite (`npm run build`) e atualização de testes automatizados.
- [x] **Fase 34: Caderno Metodológico Responsivo e Remoção de Importação de Dados Oficiais (v1.23.2)**
  - [x] Caderno Metodológico (aba `guide`) 100% responsivo para mobile, tablet e desktop.
  - [x] Paddings, títulos e badges da Nota Técnica nº 30/2025 ajustados para quebra fluida em telas estreitas.
  - [x] Fórmulas dos índices $X$ e $Y$ encapsuladas com proteção contra overflow (`overflow-x-auto whitespace-nowrap`).
  - [x] Grids de parâmetros populacionais, ponderadores e critérios de desempate adaptáveis a múltiplas resoluções.
  - [x] Tabelas analíticas de conversão de escores e repasse financeiro configuradas com rolagem horizontal fluida (`min-w-[550px]`).
  - [x] Remoção definitiva do botão "Importar CSV" e modal de upload de CSV oficial Siaps, mantendo o sistema 100% integrado aos dados reais.
  - [x] Limpeza completa de métodos e propriedades de upload no Livewire `TerritorialBondingOverview`.
  - [x] Atualização da suíte de testes de funcionalidade e compilação de assets de produção com Vite.

- [x] **Fase 35: Design Responsivo Global do Módulo Vínculo e Acompanhamento (v1.23.3)**
  - [x] Relação Nominal: barra de filtros rápidos em grid responsivo de 1 a 8 colunas, eliminando corte de texto em celulares.
  - [x] Relação Nominal: tabela de 18 colunas com proteção `min-w-[1300px]` para scroll horizontal suave no mobile.
  - [x] Relação Nominal: modais de busca avançada e prontuário com `max-h-[90vh] overflow-y-auto` e padding adaptável.
  - [x] Equipes (Mensal): painel superior de síntese com grade 2x2 no mobile e 5 colunas no desktop com divisórias perfeitas.
  - [x] Equipes (Mensal): barra de filtros em grid de 6 colunas e tabela de 14 colunas com `min-w-[1100px]`.
  - [x] Componente de Abas: correção de fechamento de tag `<a>` e tipografia fluida.
  - [x] Validação de compilação de todas as 3 abas sem erros de sintaxe ou variáveis indefinidas.

- [x] **Fase 36: Responsividade Global e Contrato de UX (v1.23.4)**
  - [x] Revisão do layout principal, gaveta mobile, login e dashboard para smartphones, tablets, notebooks e telas amplas.
  - [x] Padronização responsiva das abas de Saúde da Família, Vínculo e Acompanhamento, Configurações e Ajuda.
  - [x] Ajuste de cards, filtros, formulários, tabelas e modais em todas as telas funcionais auditadas.
  - [x] Proteção contra overflow horizontal do documento, mantendo rolagem localizada em tabelas de alta densidade.
  - [x] Áreas de toque, foco visível e ações de modais refinados para acessibilidade e uso móvel.
  - [x] Criação do contrato de UX em `UX-CONTRACT.md` e atualização das diretrizes em `DESIGN.md`.
  - [x] Teste automatizado de regressão responsiva e matriz visual validada de 320×568 a 1440×900.

- [x] **Fase 37: Navegação CVAT e Consolidação Mensal com Dados Reais (v1.23.5)**
  - [x] Correção do estado ativo da sidebar mobile e desktop para impedir destaque simultâneo de Relação Nominal e Equipes.
  - [x] Inclusão de semântica `aria-current` nos links ativos do módulo.
  - [x] Substituição de competência, datas, totais e métricas fixas da aba Equipes por consolidação mensal da relação nominal armazenada no MySQL.
  - [x] Cálculo por INE dos índices ponderados, escores e classificação final conforme os parâmetros metodológicos existentes.
  - [x] Estado de ausência explícito quando não há registros válidos, sem números demonstrativos.
  - [x] Testes automatizados de regressão para navegação, dados reais, filtros e estado vazio.

- [x] **Fase 38: Filtro de Equipes Homologadas eSF/eAP (v1.23.6)**
  - [x] Restrição da listagem mensal aos INEs homologados no CNES como eSF tipo `70` ou eAP tipo `76`.
  - [x] Exclusão de equipes de saúde bucal, eMulti e demais tipos dos totais e indicadores CVAT.
  - [x] Fallback local limitado a snapshots MySQL individuais com tipo `70/76` e INE preenchido.
  - [x] Validação da relação municipal com exatamente 19 equipes eSF homologadas.
  - [x] Teste automatizado de regressão para tipos de equipe não elegíveis.

## Reavaliação CVAT pelas NT 30/2025 e 8/2026 (21/09/2026)

Os checklists históricos das fases 32 a 38 registram a implementação da época; suas afirmações de escore oficial e consolidação completa foram superadas por esta auditoria.

- [x] Remover a geração e a exibição automática de cadastros e métricas demonstrativas.
- [x] Marcar a proveniência da extração e impedir que avaliações legadas ou Q3/2026 demonstrativas apareçam como resultado real.
- [x] Mapear o esquema real do PEC, extrair a competência corrente em leitura PostgreSQL e validar datas MICI/MICDT por amostragem.
- [x] Extrair contatos e práticas de cuidado das tabelas por cidadão; aplicar janelas de 24 e 12 meses e vínculo a equipes CNES 70/76.
- [x] Implementar as fórmulas determinísticas das duas NT com testes de limites e média quadrimestral.
- [x] Ler a última importação PBF finalizada do PEC, cruzar CPF/CNS e mostrar a quantidade confirmada com vigência; manter BPC e classificação como não aferíveis.
- [x] Processamento CVAT via fila assíncrona com prazo de reentrega superior ao timeout; deploy reinicia workers e verifica o serviço.
- [x] Mostrar ausência explícita de BPC, bônus e classificação, sem tratar ausência de dado como zero.
- [ ] Obter BPC/PBF, satisfação e quatro snapshots mensais completos, validar com Siaps e habilitar classificação quadrimestral.

## Ajuste do Painel Municipal da Saúde da Família e Busca Avançada (v1.23.11 - 23/09/2026)

- [x] Agregação real de equipes por faixa de desempenho (Ótimo, Bom, Suficiente, Regular) nos 7 indicadores clínicos oficiais (C1 a C7) no `FamilyHealthService`.
- [x] Reestruturação da tela de visão geral municipal com Hero Card centralizado e grade de 3 colunas de indicadores conforme layout de referência.
- [x] Definição padrão do quadrimestre avaliado corrente no carregamento do painel.
- [x] Centralização da seleção e troca de quadrimestre exclusivamente via modal de Busca Avançada.
- [x] Testes de regressão e validação com microdados reais do município.

## Painel do Indicador C1 (Mais Acesso Mensal) com Microdados Reais (v1.23.12 - 23/09/2026)

- [x] Reestruturação completa do detalhe do Indicador C1 em `/saude-da-familia/c1` para apresentação alinhada ao painel oficial municipal.
- [x] Microdados 100% reais carregados dos snapshots mensais de todas as 19 equipes ativas do município, sem valores mockados.
- [x] Exibição de colunas Unidade (CNES e Nome), Equipe (INE e Nome), Mês, Programado (Numerador), Espontâneo, Total (Denominador), Avaliada?, Indicador e Classificação.
- [x] Cabeçalho com legenda das 4 faixas oficiais do Ministério da Saúde: Regular, Suficiente, Bom e Ótimo.
- [x] Subabas Resumo por Equipe e Sem Equipe.
- [x] Filtros analíticos por Distrito, Unidade, Equipe, Mês, Quadrimestre e Classificação com ação Carregar.
- [x] Menu suspenso de Relatório com impressão em PDF e exportação de planilha CSV (UTF-8 BOM, ponto-e-vírgula).
- [x] Testes de regressão automatizados cobrindo versão, integridade de rotas e cálculo de indicadores.

