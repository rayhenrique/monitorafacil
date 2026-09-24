# 📦 Histórico de Versões e Atualizações · Monitora Fácil

Documento oficial de versionamento semântico (`SemVer`) e notas de lançamento (*Release Notes*) da plataforma **Monitora Fácil · Gestão da Atenção Primária à Saúde**.

## [v1.24.0] - 24/09/2026

### Indicador C5 (Hipertensão Arterial Sistêmica), 4 Boas Práticas Clínicas (A–D, 100 pts) e Busca Ativa Nominal
- **Indicador C5 com 4 Boas Práticas Clínicas (A–D) e 100 Pontos**: Implementação integral do Indicador C5 (Cuidado da Pessoa com Hipertensão na APS) com as 4 boas práticas oficiais somando 100 pontos (A: Consulta médica ou de enfermagem nos últimos 6 meses [25 pts]; B: Aferição de Pressão Arterial nos últimos 6 meses por profissional habilitado [25 pts]; C: Antropometria com Peso e Altura simultâneos na mesma data nos últimos 12 meses [25 pts]; D: Duas visitas domiciliares de ACS nos últimos 12 meses com intervalo mínimo de 30 dias [25 pts]), conforme Nota Metodológica C5 e Nota Técnica nº 08/2026-DEAPS/SAPS/MS.
- **Exclusão Normativa Estrita de ACS para Aferição de Pressão Arterial**: Cumprimento rigoroso da regra estabelecida no Quadro 03 e Rodapé 4 da Nota Metodológica C5: o CBO 5151-05 (ACS) foi expressamente excluído de pontuar a prática de aferição de PA. Apenas TACS (3222-55), técnicos, enfermeiros, médicos e outros profissionais habilitados pontuam.
- **Normalização de Equipes eAP (tipo 76)**: Implementação da regra que dispensa equipes eAP sem agente comunitário da obrigatoriedade da Prática D (Visitas ACS), normalizando a pontuação proporcionalmente para a base 100 com o fator $100 / 75$.
- **Lista Nominal e Coorte de Hipertensos com Busca Ativa**: Tabela nominal completa com 11.032 cidadãos reais cadastrados e vinculados no e-SUS PEC, busca rápida por CNS, CPF, Nome, CNES, INE e Microárea, personalização de colunas visíveis, badges circulares com status de cumprimento das 4 práticas e exportação em CSV.
- **Modal de Ficha Clínica Individual do Hipertenso**: Auditoria clínica minuciosa com histórico de diagnósticos (CIAP-2/CID-10), datas do primeiro e último diagnóstico, status da condição no PEC (ativo/resolvido) e linha a linha de conferência das 4 boas práticas com datas e valores.
- **Subaba Resumo Mensal das Equipes no C5**: Visualização consolidada de todas as 19 equipes ativas do município, distribuição em 4 faixas de desempenho (Regular, Suficiente, Bom e Ótimo) e tabela ordenada com numerador, denominador, pontuação percentual e badge oficial.
- **Integração no Módulo "Processar Dados"**: Card dedicado ao C5 com suporte a processamento pontual (`--scope=c5`) e inclusão na rotina de processamento geral.

---

## [v1.23.16] - 23/09/2026

### Indicador C4 (Diabetes Mellitus), Extração Real de Exames, Resumo de 19 Equipes e Deploy Atualizado
- **Indicador C4 com 6 Boas Práticas Clínicas (A–F) e 100 Pontos**: Implementação completa do Indicador C4 (Cuidado da Pessoa com Diabetes Mellitus na APS) com as 6 boas práticas oficiais somando 100 pontos (A: Consulta médica ou de enfermagem nos últimos 6 meses [15 pts]; B: Aferição de Pressão Arterial nos últimos 6 meses [15 pts]; C: Avaliação dos pés nos últimos 12 meses [20 pts]; D: Atendimento Odontológico nos últimos 12 meses [15 pts]; E: Hemoglobina Glicada nos últimos 6 meses [25 pts]; F: Visitas domiciliares de ACS nos últimos 6 meses [10 pts]), conforme Nota Metodológica C4 e Nota Técnica nº 08/2026.
- **Extração Real de Exames Laboratoriais de Hemoglobina Glicada (HbA1c)**: Integração avançada no DW do e-SUS PEC consultando as tabelas `tb_fat_atd_ind_exames` e `tb_fat_atd_ind_procedimentos`, capturando exames laboratoriais avaliados e elevando a cobertura real da Prática E para 81,67% (4.664 de 5.711 pessoas com diabetes).
- **Lista Nominal e Coorte de Diabéticos com Busca Ativa**: Tabela nominal detalhada com busca rápida, paginação, personalização de colunas, badges temáticos para cada uma das 6 práticas, modal de busca avançada com filtros clínicos e modal com ficha individual completa.
- **Correção no Agrupamento de Equipes no C2, C3 e C4 (19 Equipes Oficiais)**: Agrupamento estrito por código INE (`groupBy('ine')`) com agregações `MAX()` em C2, C3 e C4, eliminando duplicações resultantes de divergências históricas de CNES (como ESF 006) e assegurando exatamente as 19 equipes ativas do município.
- **Botão "Processar Tudo" com Agendamento do CVAT**: Aprimoramento do módulo "Processar Dados" para consolidar todos os indicadores da APS (C1 a C4, MICI e MICDT) e despachar o job assíncrono `SyncCvatNominalJob` na fila (`monitorafacil-queue.service`).
- **Scripts e Documentação de Deploy Padronizados (15 Etapas)**: Atualização de `deploy.sh` e `scripts/deploy.sh` com 15 etapas de execução, alocação de memória `-d memory_limit=1024M` e inclusão do Passo 13 no `deploy.md` para gerenciamento do serviço systemd de filas.
- **Correção Livewire**: Implementação do método `applyAdvancedFilters` no componente `IndicatorDetail`, solucionando erro de chamada no modal de busca avançada.

---

## [v1.23.15] - 23/09/2026

### Indicador C3 (Gestação e Puerpério), Processamento e Deploy Automatizado
- **Indicador C3 com 11 Boas Práticas Clínicas (A–K) e Multiplicador 2.0×**: Implementação completa das 11 boas práticas oficiais da gestação e puerpério somando 100 pontos (A: Captação precoce até 12ª sem [10 pts]; B: Consultas de pré-natal >= 6 [10 pts]; C: Puerpério até 42d [10 pts]; D: Atendimento Odontológico [10 pts]; E: Hemograma completo [9 pts]; F: Glicemia de jejum [9 pts]; G: Testes rápidos Sífilis/HIV [9 pts]; H: Urocultura e EAS [9 pts]; I: Vacina dTpa [9 pts]; J: Ultrassonografia obstétrica [9 pts]; K: Visitas domiciliares de ACS >= 2 [6 pts]), com multiplicador ministerial 2.0× conforme Nota Metodológica C3 e NT 08/2026.
- **Lista Nominal e Coorte de Gestantes e Puérperas com Busca Ativa**: Tabela nominal completa de 17 colunas de acompanhamento individual com cálculo de idade gestacional precisa (`19s,5d`), DUM, DPP, fim do puerpério, dados sociodemográficos, CNS profissional, microárea, status MICI e badges temáticos para cada uma das 11 práticas.
- **Modais de Busca Avançada e Ficha Clínica**: Modal interativo com filtros por equipe, microárea, faixas de idade gestacional, status de cada uma das 11 práticas; modal de detalhes com ficha territorial e cadastral completa da gestante/puérpera.
- **Subaba Resumo Mensal das Equipes no C3**: Visualização executiva consolidada de todas as 19 equipes ativas do município na competência mensal, com cards estatísticos de Distribuição das Equipes (Regular, Suficiente, Bom e Ótimo) e tabela expansível detalhando CNES, INE, Numerador de pontuação, Denominador de gestantes, Pontuação percentual e Badge oficial.
- **Comando Artisan `esus:process-data` com Suporte ao Escopo C3**: Validação e isolamento do escopo `--scope=c3` no comando CLI, garantindo que o C3 possa ser executado sob demanda sem disparar cálculos desnecessários de outros indicadores.
- **Auditoria Visual no Módulo "Processar Dados"**: Aprimoramento da interface em Configurações com suporte assíncrono para o Indicador C3, novos badges com ícones informativos de Sucesso (verde), Falha (vermelho) e Ignorado (neutro) para componentes fora do escopo selecionado.
- **Scripts de Deploy Atualizados (14 Etapas)**: Inclusão oficial do passo de consolidação do C3 (`artisan esus:process-data --scope=c3`) em `deploy.sh` e `scripts/deploy.sh` com suporte a bypass em flags `--quick`/`--no-sync` e verificação do worker `monitorafacil-queue.service`.

---

## [v1.23.14] - 23/09/2026

### Resumo Mensal das Equipes nos Indicadores C1 e C2 da Saúde da Família
- **Aba "Resumo Mensal das Equipes" no Indicador C1 (Mais Acesso)**: Implementação de visualização consolidada do desempenho de todas as 19 equipes ativas do município na competência mensal (`2026 / M9`), contendo Hero Card do indicador, bloco de Distribuição das Equipes com 4 cards estatísticos (Regular 0 [0.0%], Suficiente 0 [0.0%], Bom 3 [15.8%] e Ótimo 16 [84.2%] com barras de progresso proporcionais) e tabela expansível com Unidade (CNES), Equipe (INE), Numerador de atendimentos programados, Total de atendimentos, Pontuação e Badge de classificação ministerial.
- **Aba "Resumo Mensal das Equipes" no Indicador C2 (Cuidado no Desenvolvimento Infantil)**: Implementação de visualização consolidada do desempenho de todas as 19 equipes ativas para o cuidado infantil na competência mensal (`2026 / M9`), contendo Hero Card do indicador, bloco de Distribuição das Equipes com 4 cards estatísticos (Regular 0 [0.0%], Suficiente 8 [42.1%], Bom 11 [57.9%] e Ótimo 0 [0.0%] com barras de progresso proporcionais) e tabela expansível com Unidade (CNES), Equipe (INE), Numerador de pontuação, Denominador de crianças na coorte (1.042 crianças reais), Pontuação percentual calculada e Badge de classificação ministerial.
- **Alternância Fluida entre Subabas**: Navegação com persistência de estado e transição suave entre a visualização de Resumo Mensal Consolidado e as visões analíticas por equipe e nominal (C1: Resumo Mensal das Equipes / Resumo por Equipe / Sem Equipe; C2: Resumo Mensal das Equipes / Lista Nominal).
- **Dados 100% Reais e Validados**: Alimentação dos dados exclusivamente a partir dos snapshots e registros reais do PostgreSQL do e-SUS PEC / DW municipal, sem valores simulados ou dados mockados.

---

## [v1.23.13] - 23/09/2026

### Painel do Indicador C2 (Cuidado no Desenvolvimento Infantil) com Microdados Reais do PEC DW
- **Microdados 100% Reais da Coorte de Crianças**: Extração e cálculo integral de 1.042 crianças reais (0 a 24 meses) vinculadas às 19 equipes de saúde da família do município a partir do banco de dados PostgreSQL do e-SUS PEC, eliminando qualquer dado demonstrativo ou mockado.
- **Estrutura Visual e Síntese das 5 Boas Práticas**: Banner superior dividido em 3 blocos analíticos: Bloco Mês da Coorte (`2026 / M9`), Bloco Central com quantitativos e percentuais em verde das 5 práticas oficiais (A: Consulta até 30d, B: Consultas de Puericultura >= 9, C: Peso e Altura >= 9, D: Visitas Domiciliares ACS >= 2, E: Vacinas completas) e Bloco Denominador com o total de crianças na coorte.
- **Barra de Filtros Rápidos e Paginação**: Campos de busca instantânea por CNS, CPF, Nome da Criança, CNES, INE e quantidade de registros por página (com 30 itens selecionados por padrão).
- **Personalização de Colunas Visíveis**: Menu suspenso com contador "Colunas visíveis: 10 itens selecionados" configurado por padrão, permitindo alternar a exibição e restaurar os campos originais.
- **Tabela Nominal Interativa**: Lista detalhada com CNS e CPF mascarados no padrão oficial, botão de revelar/copiar, data de nascimento, nome, idade em meses, raça/cor, CNS profissional com tooltip descritivo do nome, mês da coorte, microárea, indicador de MICI Atualizada (Sim/Não) e badges compactos para o status de cada boa prática (verde quando cumprida, vermelho quando pendente).
- **Modais de Busca Avançada e Detalhes Clínicos**: Modal de busca com filtros aprofundados por equipe, microárea, responsáveis, faixas etárias e cumprimento de práticas; modal de detalhes com ficha territorial, cadastral e situação clínica individual de cada uma das 5 práticas recomendadas pela Nota Metodológica C2 e Portaria GM/MS nº 3.493/2024.
- **Rodapé Institucional**: Inclusão de rodapé oficial com versão do sistema e créditos de desenvolvimento.

---

## [v1.23.12] - 23/09/2026

### Painel do Indicador C1 (Mais Acesso Mensal) com Microdados Reais
- **Alinhamento Fiel ao Painel Oficial de Mais Acesso**: Reestruturação completa da tela `/saude-da-familia/c1` para apresentação detalhada dos atendimentos individuais da atenção primária, respeitando integralmente os dados reais consolidados de todas as 19 equipes homologadas do município.
- **Tabela Analítica por Equipe**: Visualização com colunas de Unidade (CNES e Nome), Equipe (INE e Nome), Mês de competência, Atendimentos Programados (Numerador), Atendimentos Espontâneos, Total de Atendimentos (Denominador), Indicador de Avaliação Homologada, Barra de Progresso com Percentual e Badge de Classificação.
- **Cabeçalho com Legenda de Parâmetros Ministeriais**: Apresentação das faixas de classificação com cores padronizadas da Atenção Primária: *Regular* (<= 10% ou > 70%), *Suficiente* (> 10% e <= 30%), *Bom* (> 30% ou <= 50%) e *Ótimo* (> 50% ou <= 70%).
- **Subabas Resumo por Equipe e Sem Equipe**: Possibilidade de navegação entre o resumo individualizado por equipes de saúde da família e atendimentos não vinculados a equipes.
- **Filtros Dinâmicos e Exportação**: Filtros por Distrito, Unidade, Equipe, Mês, Quadrimestre e Classificação com botão Carregar, além de menu suspenso de Relatório com impressão em PDF e download de planilha CSV (UTF-8 BOM, ponto-e-vírgula).

---

## [v1.23.11] - 23/09/2026

### Painel Municipal da Saúde da Família e Busca Avançada
- **Exibição Dinâmica das Faixas de Desempenho (C1 ao C7)**: Adequação visual da tela municipal de Saúde da Família com a distribuição real de equipes homologadas nas faixas *Ótimo*, *Bom*, *Suficiente* e *Regular*, calculadas diretamente a partir das avaliações e notas técnicas oficiais (Portaria GM/MS nº 3.493/2024 e NTs 08/2026).
- **Hero Card Centralizado**: Cartão principal com ícone de cuidado e identificação direta do quadrimestre avaliado.
- **Padrão Automático do Quadrimestre Avaliado**: Ao acessar o módulo sem parâmetros na URL, o sistema seleciona automaticamente o quadrimestre avaliado do período corrente.
- **Seleção de Quadrimestres na Busca Avançada**: Remoção de seletores redundantes no Hero Card e centralização da escolha e alternância de quadrimestres exclusivamente no modal de Busca Avançada.

---

## [v1.23.10] - 22/09/2026

### Correção de Métricas por Equipe e Busca Avançada na Relação Nominal
- **Correção de Atributos de Agregação por Equipe**: Em `CvatNominalDwService`, inclusão da cópia dos atributos de benefício e proveniência (`benefit_data_available`, `source`, `excluded_without_pec_id`, `pbf_import_id`, `pbf_vigencia`, `pbf_confirmed_total`) para o objeto `stdClass` resultante da consulta agregada por equipe.
- **Prevenção de Erro 500 no Livewire**: Acesso defensivo com `! empty($metrics->benefit_data_available)` na view Blade da Relação Nominal (`nominal-list.blade.php`), eliminando a exceção `ErrorException: Undefined property: stdClass::$benefit_data_available` ao filtrar por equipes e abrir o modal de Busca Avançada.

---

## [v1.23.9] - 21/09/2026

### Filtro Dinâmico por Equipe nos Cards e Exportação CSV/PDF da Relação Nominal
- **Atualização Dinâmica dos Cards por Equipe**: Ao selecionar qualquer equipe na Relação Nominal (pelo seletor do cabeçalho ou busca avançada), os cards de **Dimensão Cadastro** e **Dimensão Acompanhamento** têm seus quantitativos e percentuais recalculados em tempo real a partir dos microdados da equipe selecionada.
- **Seletor de Equipe no Cabeçalho**: Adicionado seletor com todas as equipes ativas do município diretamente na barra de ações da Relação Nominal, com badge indicativo no cabeçalho dos cards e botão de limpeza para retornar ao consolidado municipal.
- **Exportação Nominal em CSV (Excel)**: Adicionado botão de exportação que gera planilha em formato `.csv` com delimitador ponto-e-vírgula (`;`), cabeçalho completo de auditoria e marcador UTF-8 BOM para abertura imediata no Microsoft Excel sem distorção de caracteres ou acentuação gráfica. O download é realizado via streaming otimizado para suportar bases completas.
- **Exportação Nominal em PDF (A4 Paisagem)**: Geração de relatório institucional em PDF no formato A4 paisagem via `Barryvdh\DomPDF`, contendo brasão/nome do município, competência, identificação da equipe, resumo dos indicadores de cadastro e acompanhamento e tabela nominal detalhada.

---

## [v1.23.8] - 21/09/2026

### Cards Nominais CVAT, Filtro por Equipe e Toggle do Sidebar
- **Cards de Dimensão Cadastro na Relação Nominal**: Estrutura métrica com dados reais da competência atual (`2026/M09`), detalhando Total Geral de MICI, MICI Atualizados/Desatualizados, MICI Sem MICDT, MICI Atua. e MICDT Desat. ou Sem, MICI Atualizados e Sem MICDT, MICI Com MICDT, MICI e MICDT Atualizados/Desatualizados e Cidadãos Vinculados/Não Vinculados.
- **Accordion de Dimensão Acompanhamento**: Painel com 4 colunas mutuamente exclusivas seguindo a Nota Técnica nº 30/2025 (*Sem Critério*, *Idoso ou Criança*, *BPC ou PBF*, *Idoso ou Criança + BPC ou PBF*), exibindo quantitativos reais de acompanhados e não acompanhados.
- **Busca Avançada com Filtro de Equipe**: Adicionado seletor de Equipe no modal de busca avançada com todas as equipes ativas do município para filtragem nominal ágil.
- **Módulo de Equipes (Mensal)**: Restaurado o carregamento e consolidação das 19 equipes ativas do município a partir da base local, exibindo indicadores de cadastro (X), acompanhamento (Y) e classificações finais (Ótimo, Bom, Suficiente e Regular).
- **Toggle do Sidebar**: Adicionado controle de alternância no menu lateral (cabeçalho da sidebar e barra superior) para recolher em modo compacto de ícones (`w-20`) e expandir (`w-72`), com persistência de preferência no `localStorage`.

---

## [v1.23.7] - 21/09/2026

### Vínculo e Acompanhamento: extração real e PBF identificado
- A relação nominal da competência corrente usa dados do PEC em leitura PostgreSQL e salva o resultado no MySQL local, com proveniência e data de referência.
- A última importação PBF finalizada no PEC é cruzada por CPF ou CNS. A tela mostra o beneficiário, a vigência da importação e o total elegível, com filtro nominal PBF.
- Contatos e práticas de cuidado, atualização MICI/MICDT e vínculo a equipes eSF/eAP seguem as janelas e os critérios examinados nas NT 30/2025 e 8/2026. A rotina de extração é agendada fora da requisição web.
- O botão de processamento CVAT exige fila assíncrona e worker ativo; o deploy reinicia os workers e verifica o serviço. O prazo de reentrega da fila supera o tempo máximo do job.
- Dados e classificações demonstrativos deixam de aparecer como aferição. Como BPC e a série mensal completa ainda não foram confirmados, os quadrantes ponderados, o índice Y, a classificação final e o repasse permanecem não aferíveis.
- Validação local: importação PEC 202602 finalizada; 8.924 beneficiários PBF com cadastro elegível no filtro nominal. Os arquivos nominais de importação não integram o repositório.

---

## [v1.23.6] - 19/09/2026

### 🩺 Filtro de Equipes Homologadas eSF/eAP
- A aba `Equipes (Mensal)` agora consolida somente os INEs homologados como eSF tipo `70` ou eAP tipo `76`.
- O XML CNES local é usado como fonte principal da relação oficial de equipes ativas.
- Na ausência do XML, o fallback consulta apenas snapshots MySQL individuais com INE preenchido e tipo `70/76`.
- Equipes de saúde bucal, eMulti e quaisquer outros tipos deixam de participar dos totais, indicadores e linhas da tabela.
- Validação local confirmou exatamente 19 equipes eSF homologadas na competência disponível.
- Teste automatizado incluído para impedir a reintrodução de equipes não elegíveis.

---

## [v1.23.5] - 19/09/2026

### 🔗 Navegação CVAT e Consolidação Mensal com Dados Reais
- **Navegação sem ambiguidade**:
  - Correção do estado ativo da sidebar mobile e desktop para destacar somente `Relação Nominal` na rota nominal e somente `Equipes (Mensal)` na aba de equipes.
  - Inclusão de `aria-current="page"` nos links ativos para tornar o estado atual também perceptível por tecnologias assistivas.
- **Dados mensais derivados da base local**:
  - Remoção dos totais, competência, data de atendimento, quantidade de equipes e detalhes por INE que estavam fixos na interface e no serviço.
  - Consolidação por equipe calculada a partir de `cvat_nominal_citizens`, usando o mês mais recente da competência selecionada no MySQL.
  - Cálculo dos índices ponderados de cadastro e acompanhamento, escores e classificação final por equipe com os parâmetros metodológicos existentes.
  - Estado vazio explícito quando não há registros nominais válidos, sem substituir ausência de dados por valores demonstrativos.
  - Badges, cabeçalho, resumo e rodapé atualizados para refletir a quantidade e o período realmente encontrados.
- **Qualidade**:
  - Cobertura automatizada para estados ativos da sidebar, consolidação de duas equipes, filtros e ausência de dados.
  - Compatibilidade das migrations de consolidação com o banco SQLite usado na suíte de testes, sem alterar as restrições aplicadas no MySQL.

---

## [v1.23.4] - 19/09/2026

### 📱 Responsividade Global e Contrato de UX
- **Cobertura completa da aplicação**:
  - Layout autenticado, gaveta mobile, tela de acesso e dashboard revisados para celulares, tablets, notebooks e monitores amplos.
  - Abas e cabeçalhos dos módulos de Saúde da Família, Vínculo e Acompanhamento, Configurações e Ajuda com navegação horizontal segura e alvos de toque consistentes.
  - Formulários, filtros e grupos de ações reorganizados progressivamente, evitando campos comprimidos e botões fora da viewport.
  - Tabelas de alta densidade preservadas em contêineres de rolagem horizontal, com indicação visual para usuários de telas pequenas.
  - Modais ajustados à altura útil da tela, com cabeçalhos, conteúdo rolável e rodapés de ação estáveis.
- **Qualidade e manutenção**:
  - Criação do `UX-CONTRACT.md` com regras duráveis de layout, navegação, tabelas, formulários, modais, acessibilidade e estados de interface.
  - Ampliação do `DESIGN.md` com decisões responsivas globais do produto.
  - Inclusão de teste automatizado cobrindo os padrões estruturais críticos de responsividade.
  - Validação visual nas resoluções 320×568, 390×844, 768×1024, 1024×768 e 1440×900, sem overflow no documento.

---

## [v1.23.3] - 19/09/2026

### 🎨 Design Responsivo Global do Módulo Vínculo e Acompanhamento
- **Auditoria de Responsividade Completa**:
  - **Aba Relação Nominal (`/vinculo-e-acompanhamento/relacao-nominal`)**:
    - Cabeçalho responsivo com botão de busca avançada adaptável (`w-full sm:w-auto`).
    - Matriz da Dimensão Cadastro e Dimensão Acompanhamento com paddings e bordas proporcionais para smartphones e tablets.
    - Barra de filtros rápidos reestruturada em grid adaptativo de 1 a 8 colunas (`grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8`), eliminando campos espremidos no celular.
    - Controles de paginação, dropdowns e contador de registros organizados em pilha vertical no mobile e linha única no desktop.
    - Tabela nominal de 18 colunas com proteção de largura mínima (`min-w-[1300px]`), permitindo rolagem horizontal suave sem deformação de dados.
    - Modais de Busca Avançada e Prontuário de Detalhes ajustados com `max-h-[90vh] overflow-y-auto` e padding adaptável.
  - **Aba Equipes Mensal (`/vinculo-e-acompanhamento?aba=teams`)**:
    - Painel superior de síntese com grade 2x2 no mobile e 5 colunas no desktop, com divisórias estéticas limpas sem linhas órfãs.
    - Barra de filtros reorganizada em grid responsivo de 6 colunas (`grid-cols-2 sm:grid-cols-3 lg:grid-cols-6`), com alinhamento uniforme.
    - Tabela de equipes de 14 colunas com largura mínima garantida (`min-w-[1100px]`).
    - Modal de busca avançada com scroll vertical automático em telas de baixa altura.
  - **Componente de Abas (`territorial-bonding-tabs.blade.php`)**:
    - Correção de fechamento de tags HTML e refinamento de tipografia do título e badges.

---

## [v1.23.2] - 19/09/2026

### 📱 Caderno Metodológico Responsivo e Remoção de Importação Manual
- **Caderno Metodológico 100% Responsivo**:
  - Ajuste de todo o layout do Caderno Metodológico (`/vinculo-e-acompanhamento?aba=guide`) para dispositivos móveis, tablets e desktops:
    - Espaçamentos e paddings adaptativos (`p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8`).
    - Quebra harmônica dos badges normativos da Nota Técnica nº 30/2025 e Portaria SAPS nº 161/2024.
    - Fórmulas matemáticas dos índices $X$ e $Y$ com contêiner `overflow-x-auto whitespace-nowrap`, evitando quebras indesejadas de largura em smartphones.
    - Grids de parâmetros populacionais, ponderadores e critérios de desempate adaptáveis (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`).
    - Tabelas analíticas de conversão de escores (Índice X, Índice Y e Escore Final/Repasse) encapsuladas com rolagem horizontal suave (`min-w-[550px]`).
- **Remoção Definitiva da Importação Manual de CSV**:
  - Eliminação do botão "Importar CSV" no cabeçalho de monitoramento de equipes.
  - Remoção de todo o modal de upload de arquivos CSV do Siaps e código residual do Livewire (`WithFileUploads`, `$showImportModal`, `$csvFile`, etc.).
  - Sistema consolidado para operar exclusivamente com os dados reais do município e extrações nativas do PostgreSQL do e-SUS PEC.

---

## [v1.23.1] - 19/09/2026

### 📊 Monitoramento de Vínculo e Acompanhamento - Equipes (Mensal) com Dados Reais
- **Remoção de Abas Obsoletas**:
  - Remoção definitiva das abas `cadastro` e `acompanhamento` em Vínculo e Acompanhamento Territorial.
  - Redirecionamento automático e transparente de qualquer URL antiga com `?aba=cadastro` ou `?aba=acompanhamento` para a tela de monitoramento de equipes (`?aba=teams`).
  - Navegação do módulo agora organizada de forma limpa em: **Relação Nominal**, **Equipes (Mensal)** e **Caderno Metodológico**.
- **Painel Superior de Síntese Fidedigno**:
  - Exibição destacada de **Mês (`2026 / M9`)**, **Total Ótimo (`10 (52.63%)`)**, **Total Bom (`7 (36.84%)`)**, **Total Suficiente (`1 (5.26%)`)** e **Total Regular (`1 (5.26%)`)**, refletindo com exatidão as 19 equipes eSF de Teotônio Vilela/AL.
  - Subtítulo com data do último atendimento registrado: `18/09/2026`.
- **Tabela Analítica Completa com 14 Colunas**:
  - 14 colunas padronizadas: `CNES`, `UNIDADE`, `INE`, `EQUIPE`, `TIPO` (badge verde ESF), `PARÂMETRO CADASTRO` (2500), `CADASTROS VINCULADOS` (dados reais somando 35.401 munícipes), `C.VINC/PARAM. (%)`, `RESULTADO CADASTRO`, `SCORE CADASTRO (X)`, `RESULTADO ACOMPANHAMENTO`, `SCORE ACOMPANHAMENTO (Y)`, `SCORE FINAL (X+Y)` e `CLASSIFICAÇÃO FINAL`.
  - Formatação precisa dos dados em formato numérico conforme especificação oficial do Ministério da Saúde.
- **Barra de Filtros e Busca Avançada**:
  - Inputs dedicados para `CNES`, `UNIDADE`, `INE`, `EQUIPE`, seletor de `Classificação Final` e paginação dinâmica (`10`, `15`, `30`, `50`, `100`).
  - Modal interativo de **Busca Avançada** com filtros combinados de faixas de escores, tipos de equipe e conceitos.

---

## [v1.23.0] - 19/09/2026

### 🏛️ Alinhamento Integral à Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS & Portaria SAPS nº 161/2024
- **Definição Oficial de Pessoa Acompanhada (Item 2.6.4 da NT 30/2025)**:
  - Exigência de **mais de um contato assistencial no período de um ano (12 meses)** ($\ge 2$ contatos) anteriores à data final do quadrimestre avaliado.
  - Obrigatoriedade de que **pelo menos um contato seja Prática de Cuidado** (atendimento clínico individual médico/enfermeiro, atendimento odontológico individual, visita domiciliar de ACS ou atividade coletiva).
  - O segundo contato pode ser outra prática de cuidado ou registro de procedimentos (vacinação ou procedimentos gerais ambulatoriais).
- **Ajuste da Vulnerabilidade Infantil**:
  - Corte etário de criança corrigido estritamente para **até 5 anos incompletos (4 anos, 11 meses e 29 dias / `$age < 5` anos)** na data final do quadrimestre, conforme itens 2.2 "b" e 3.10 da NT 30/2025, alinhado às metas da primeira infância do Ministério da Saúde.
  - Critério de idoso mantido em $\ge 60$ anos e benefícios sociais PBF/BPC preservados.
- **Exclusões Cadastrais Válidas (Dimensão Cadastro)**:
  - Cadastros individuais com indicação de *"Fora de Área (FA)"* ou *"Mudança de Território (Mudou-se)"* são desconsiderados da apuração da Dimensão Cadastro.
  - Cadastros rápidos simplificados possuem fator zero na apuração.
- **Painel Executivo e Apuração dos Índices Ponderados X e Y**:
  - Implementação das fórmulas oficiais do **Índice Ponderado de Cadastro ($X$)** (ponderadores 0,75 e 1,50) e **Índice Ponderado de Acompanhamento ($Y$)** (ponderadores 1,0; 1,2; 1,3 e 2,5).
  - Escores oficiais calculados e exibidos em tempo real: Escore $X$ (até 3,00 pts), Escore $Y$ (até 7,00 pts), Escore Final (até 10,00 pts) e Classificação Ministerial (Ótimo, Bom, Suficiente, Regular) com base no parâmetro de 47.500 munícipes de Teotônio Vilela (19 eSF).
- **Caderno Metodológico Reformulado (Aba Guide)**:
  - Reformulação completa da documentação técnica no sistema, reproduzindo a íntegra da metodologia, tabelas de escore, regras de repasse financeiro, bonificação de até +0,30 pts por avaliações no aplicativo Meu SUS Digital e critérios de desempate de vínculo de cidadão com equipes.

---

## [v1.22.4] - 19/09/2026

### 📑 Relação Nominal como Submódulo Principal de Vínculo e Acompanhamento
- **Remoção de "Painel Oficial CVAT"**:
  - Eliminação definitiva da aba e do item de menu "Painel Oficial CVAT" na sidebar (desktop e mobile) e no componente de abas do módulo.
- **Relação Nominal como Primeiro Submódulo**:
  - A **Relação Nominal e Busca Ativa** foi promovida a submódulo principal e primeiro item no menu e na navegação de abas.
  - Ao clicar em **Vínculo e Acompanhamento** no menu principal ou nos atalhos do Dashboard, o sistema abre diretamente a Relação Nominal (`/vinculo-e-acompanhamento/relacao-nominal`).
- **Acesso Fluido às Demais Dimensões**:
  - O acesso às dimensões analíticas (**Dimensão Cadastro**, **Dimensão Acompanhamento**, **Desempenho das Equipes** e **Caderno Metodológico**) segue plenamente funcional através das abas e dos subitens do menu lateral.

---

## [v1.22.3] - 19/09/2026

### 🎨 Redesign Responsivo do Painel de Processamento de Dados
- **Eliminação de Esmagamento Visual e Quebra de Layout**:
  - Reestruturação da seção superior do processamento de dados (`/configuracoes/processar-dados`), separando verticalmente o bloco de apresentação conceitual dos gatilhos de execução.
  - O cabeçalho agora ocupa 100% da largura útil do container, exibindo com destaque as diretrizes de cofinanciamento federal (Portaria GM/MS 3.493/2024), sincronização direta com o PostgreSQL do e-SUS PEC e textos explicativos sem compressão lateral.
- **Grid Adaptativo com 5 Cards de Ação Temáticos**:
  - Criação de um grid responsivo (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5`) que se ajusta fluidamente em smartphones (1 coluna), tablets (2 ou 3 colunas) e desktops (5 colunas simétricas).
  - Cards temáticos com paleta e identidade visual exclusiva:
    1. **Vínculo & Território (CVAT)**: Tom verde esmeralda com foco em MICI, MICDT e Relação Nominal da busca ativa.
    2. **Mais Acesso (C1)**: Tom teal focado em atendimentos médicos e de enfermagem.
    3. **Crianças (C2)**: Tom índigo focado em desenvolvimento infantil e vacinação.
    4. **Gestantes (C3)**: Tom rose focado em pré-natal e puerpério.
    5. **Processamento Geral (Completo)**: Tom slate 900 escuro corporativo com badge dourada/teal para execução irrestrita de todas as tabelas.
- **Micro-interações e Feedback de Carregamento**:
  - Badges de escopo, divisórias sutis, transições de foco/hover suaves e spinners dedicados em cada card via Livewire (`wire:loading`).
  - Barra de progresso estimada e real vinculada com precisão através da inclusão de `processCvat` nas diretivas `wire:target`.

---

## [v1.22.2] - 19/09/2026

### 📌 Regra Estrita de 24 Meses para MICI e MICDT (Dimensão Cadastro)
- **Critério de Atualização Cadastral**:
  - Um cadastro individual (**MICI**) ou domiciliar (**MICDT**) **SÓ é considerado desatualizado se tiver mais de 24 meses** contados a partir da data de encerramento do quadrimestre avaliado (`mici_date < $cutoffMici`).
  - Caso o cidadão tenha cadastro ou atualização em menos de 24 meses (`mici_date >= $cutoffMici`), ele é rigorosamente classificado como **atualizado (`mici_updated = true`)**.
  - A mesma regra se aplica ao **MICDT**: quando o domicílio vinculado foi cadastrado ou atualizado em menos de 24 meses do fim do período, recebe status **atualizado (`micdt_updated = true`)**; somente fichas com mais de 24 meses recebem status desatualizado.
- **Precisão Temporal sem Transbordamento**:
  - Utilização de `subMonthsNoOverflow(24)` garantindo que o cálculo de dois anos retroativos a partir de `30/04`, `31/08` ou `31/12` mantenha a data exata do mês sem distorções de dias.

---

## [v1.22.1] - 19/09/2026

### ⏱️ Janela de Acompanhamento de 12 Meses no CVAT
- **Ajuste da Regra de Acompanhamento Territorial (Dimensão Acompanhamento)**:
  - Parametrizada a janela móvel para **12 meses (365 dias)** contados regressivamente a partir do último dia do quadrimestre avaliado (30/04 para Q1, 31/08 para Q2 e 31/12 para Q3).
  - Qualquer cidadão com registro de visita domiciliar de ACS (`tb_fat_visita_domiciliar`) ou atendimento clínico individual (`tb_fat_atendimento_individual`) dentro dessa janela de 365 dias é classificado como acompanhado (`is_accompanied = true`).
- **Filtro Temporal Estrito no PostgreSQL**:
  - As consultas em lote no PostgreSQL do PEC filtram explicitamente eventos até a data final do quadrimestre avaliado (`dt_registro <= $quarterEndDate`), impedindo que registros futuros distorçam o cálculo quadrimestral.
- **Idade Referenciada ao Fim do Período**:
  - A idade de cada cidadão é calculada com base na data final do quadrimestre avaliado (`diffInYears($quarterEndDate)`), assegurando o correto enquadramento nos critérios de vulnerabilidade de crianças ($< 6$ anos) e idosos ($\ge 60$ anos).

---

## [v1.22.0] - 19/09/2026

### 🚀 Extração Real Completa do DW e-SUS PEC (Relação Nominal e Métricas)
- **Extração Massiva em Lotes via Conexão Oficial (`pgsql_esus`)**:
  - Implementado motor de alta performance que lê diretamente da visão `tb_acomp_cidadaos_vinculados` do banco PostgreSQL do e-SUS PEC.
  - Paginação baseada em cursor seek por ID (`co_fat_cidadao_pec > $lastId`) com complexidade $O(1)$, permitindo extrair os mais de 36.900 cidadãos do município em poucos segundos sem risco de esgotamento de memória.
- **Cruzamento em Lote com Fichas de Cadastro e Atendimentos do PEC**:
  - Cruzamento em lote com `tb_fat_cad_individual` e `tb_dim_tempo` para determinar com exatidão a data do cadastro individual (MICI) e avaliar se está atualizado nos últimos 24 meses.
  - Identificação de vínculo domiciliar ativo (MICDT) e sua respectiva atualização em 24 meses.
  - Verificação de visitas domiciliares de ACS (`tb_fat_visita_domiciliar`) e atendimentos clínicos (`tb_fat_atendimento_individual`) para sinalizar cidadãos acompanhados no quadrimestre / últimos 120 dias.
- **Consolidação Matemática das Métricas**:
  - Eliminação de dados fixos/amostrais na extração oficial em produção: os cards das Dimensões Cadastro e Acompanhamento são calculados e consolidados diretamente a partir da base real sincronizada.
  - Coerência matemática absoluta entre os totais exibidos nos cards gerenciais e a listagem nominal da busca ativa.
- **Resiliência e Diagnóstico de Conexão**:
  - Comando `php artisan cvat:sync-nominal` e botão "Processar Vínculo e Acompanhamento" com relatório passo a passo em tempo real.
  - Fallback gracioso que preserva a base local e exibe alertas explicativos quando a aplicação for executada fora da rede do PEC (ambiente local de desenvolvimento).

---

## [v1.21.1] - 19/09/2026

### ⚙️ Processamento Exclusivo e Importação Opcional do Siaps
- **Botão Exclusivo em "Processar Dados"**:
  - Nova ação dedicada **"Processar Vínculo e Acompanhamento"** na tela de *Configurações > Processar Dados*.
  - Sincroniza sob demanda os indicadores da Dimensão Cadastro, Dimensão Acompanhamento e a Relação Nominal da busca ativa com feedback em tempo real e relatório de linhas auditadas.
- **Importação do Siaps Opcional e Desacoplada do Deploy**:
  - A importação automática do Siaps foi removida do script de deploy contínuo (`scripts/deploy.sh` e `deploy.sh`), tornando-a uma operação opcional e sob controle do gestor.
- **Upload de Arquivo CSV Diretamente no Módulo**:
  - Adicionado botão e modal interativo **"Importar CSV Siaps (Opcional)"** no painel do módulo *Vínculo e Acompanhamento*.
  - Suporte ao upload de arquivos CSV exportados do Siaps (tanto de Desempenho Quadrimestral das Equipes quanto de Distribuições das Dimensões).
  - Opção para restaurar os dados oficiais padrão do servidor diretamente pelo modal com um clique.

---

## [v1.21.0] - 19/09/2026

### 📋 Submódulo Relação Nominal e Busca Ativa no PEC (Vínculo e Acompanhamento)
- **Painel Gerencial Fidedigno (Dimensão Cadastro)**:
  - Mês de referência `2026 / M12` e data do último atendimento registrado.
  - Indicadores exatos: Total Geral de MICI (**36.951**), MICI Atualizados (**36.751** - 99,46%), MICI Desatualizados (**200** - 0,54%).
  - Total Geral de MICI Sem MICDT (**2.047**), MICI Atua. e MICDT Desat. ou Sem (**2.061** - 5,58%), MICI Atualizados e Sem MICDT (**1.947** - 95,11%).
  - Total MICI Com MICDT (**34.904** - 94,46%), MICI e MICDT Atualizados (**34.690** - 99,39%), MICI e MICDT Desatualizados (**214** - 0,61%).
  - Cidadãos Vinculados (**35.401** - 95,81%) e Não Vinculados (**1.550** - 4,19%).
- **Painel Retrátil da Dimensão Acompanhamento**:
  - Matriz completa de 4 colunas cruzando critérios de vulnerabilidade com status de acompanhamento:
    - *Sem Critério*: Total 20.550 | Acompanhados 19.348 | Não acompanhados 1.202.
    - *Idoso ou Criança*: Total 7.617 | Acompanhados 7.544 | Não acompanhados 73.
    - *BPC ou PBF*: Total 7.688 | Acompanhados 7.527 | Não acompanhados 161.
    - *Idoso/Criança + BPC/PBF*: Total 1.096 | Acompanhados 1.085 | Não acompanhados 11.
- **Barra de Filtros e Busca Ativa**:
  - Filtros rápidos por CNS, CPF, Nome do Cidadão, CNS do Profissional, Nome do Profissional, CNES, INE, Raça/Cor (R/C) e paginação por página (10, 15, 30, 50, 100).
- **Tabela Nominal de Alta Densidade com LGPD**:
  - 18 colunas detalhadas (#, CNS, CPF, CPF/CNS Responsável, Nascimento, Nome, Idade, R/C, Unidade, Equipe, Profissional, Microárea, MICI Atualização, MICDT Atualização, Vulnerabilidade Idade, BPC/PBF, Acompanhada, Ações).
  - Máscara LGPD instantânea com botão de revelar/ocultar olho e botão de copiar para a área de transferência.
  - Badges coloridos idênticos às telas de referência (Verde para atualizado/sim, Vermelho para desatualizado/sem micdt, Laranja para idoso/criança).
- **Modais Integrados**:
  - *Busca Avançada*: modal com filtros combinados de status MICI, MICDT, vinculação e vulnerabilidade.
  - *Prontuário de Vínculo (Detalhes)*: modal com ficha completa do cidadão, dados cadastrais, endereço domiciliar, equipe e profissional de referência e histórico de visitas do ACS.
- **Comando Artisan e Pipeline**:
  - `php artisan cvat:sync-nominal` para sincronização com o banco do e-SUS PEC e fallback para base de dados local.
  - Integração no `scripts/deploy.sh` e `deploy.sh`.

---

## [v1.20.0] - 19/09/2026

### 🗺️ Módulo Vínculo e Acompanhamento Territorial (Componente II - CVAT)
- **Posicionamento de Destaque**: O novo módulo "Vínculo e Acompanhamento" passa a integrar a navegação principal da aplicação, posicionado estrategicamente antes do módulo de *Saúde da Família* na barra lateral (desktop e mobile).
- **Resultados Oficiais Siaps**: Incorporação dos dados oficiais dos 3 últimos quadrimestres homologados pelo Ministério da Saúde: **Q2/2025**, **Q3/2025** e **Q1/2026** (com nota explícita de que **Q2/2026** ainda aguarda publicação ministerial).
- **Gráficos Espelhados do Siaps**: Reprodução visual fidedigna dos dois gráficos oficiais do Ministério da Saúde com barras horizontais empilhadas em 4 faixas normativas (Regular, Suficiente, Bom e Ótimo):
  - **CVAT - Dimensão Cadastro - eSF** (Peso 3 / 0,00 a 3,00 pts - cadastros individuais MICI atualizados em 24 meses).
  - **CVAT - Dimensão Acompanhamento - eSF** (Peso 7 / 0,00 a 7,00 pts - acompanhamento territorial e visitas de ACS).
- **Detalhamento das 19 Equipes eSF**: Tabela nominal completa das 19 equipes de Teotônio Vilela/AL com CNES, Unidade de Saúde, INE, notas de Cadastro, notas de Acompanhamento, Nota Final até 10,00 pts e Classificação Final com badge colorido.
- **Enquadramento Financeiro (Quadro 5 da NT 08/2026)**: Faixas oficiais de classificação para incentivo federal: Ótimo (`> 8,5`), Bom (`≥ 7,0 e ≤ 8,5`), Suficiente (`≥ 5,0 e < 7,0`) e Regular (`< 5,0`). O município alcançou média **8,77 / 10,00** no Q1/26 com classificação **ÓTIMO** (100% de repasse).
- **Integração com o Dashboard**: O card de Vínculo e Território no painel inicial foi conectado às contagens oficiais do Siaps, exibindo a distribuição real das equipes e link direto para o novo módulo.
- **Importação Automatizada**: Novo comando Artisan `php artisan cvat:import-siaps` e serviço `CvatService` para leitura e carga idempotente dos arquivos CSV do Siaps.

---

## [v1.19.0] - 19/09/2026

### 📊 Indicadores Reais C1–C3 no Dashboard
- **Fim dos zeros fixos**: os cartões C1, C2 e C3 deixam de apresentar valores definidos diretamente no componente visual.
- **Snapshots reais**: as quantidades de equipes em Ótimo, Bom, Suficiente e Regular são lidas de `family_health_indicator_snapshots` para o ano e quadrimestre selecionados.
- **Sem duplicidade municipal**: somente registros com INE são contados; o consolidado municipal não entra na distribuição das equipes.
- **Versão vigente**: cada snapshot precisa corresponder à versão atual do cálculo C1, C2 ou C3, impedindo a exibição de dados antigos ou simulados.
- **Ausência explícita**: quando não há consolidação válida, o cartão informa a ausência em vez de exibir quatro zeros como se fossem resultado real.

---

## [v1.18.0] - 19/09/2026

### ⏳ Progresso Visível no Processamento
- **Feedback imediato**: os botões de C1, C2, C3 e Geral Completo exibem uma barra assim que o processamento começa.
- **Progresso estimado**: a barra avança até 92% enquanto o servidor trabalha, sem apresentar a estimativa como percentual clínico ou resultado confirmado.
- **Etapas legíveis**: conexão, validação, consolidação e finalização aparecem em texto durante a espera.
- **Resultado real**: o retorno do servidor substitui a estimativa por 100% e informa sucesso ou pendências; os botões voltam a ficar disponíveis.
- **Acessibilidade**: o progresso possui semântica `progressbar`, descrição textual, estado de carregamento e animação reduzida quando solicitado pelo sistema.

---

## [v1.17.1] - 19/09/2026

### 🔧 Compatibilidade da Dimensão da DUM no C3
- **DW atual**: `co_dim_tempo_dum` continua sendo resolvida por `tb_dim_tempo_dum.co_seq_dim_tempo_dum` quando essa dimensão está disponível.
- **DW legado**: instalações em que a mesma coluna referencia `tb_dim_tempo.co_seq_dim_tempo` passam a ser reconhecidas automaticamente.
- **Diagnóstico**: a extração só é interrompida quando nenhuma das duas dimensões contém chave e data compatíveis.

---

## [v1.17.0] - 19/09/2026

### 🤰 Revisão Normativa e de Desempenho do C3
- **Correção do timeout**: removida a subconsulta correlacionada sobre procedimentos e o histórico sem data inicial; a extração usa lotes de 100 cidadãos, datas delimitadas e `statement_timeout` de 30 segundos somente durante o C3.
- **DUM oficial**: `co_dim_tempo_dum` passa a referenciar `tb_dim_tempo_dum.co_seq_dim_tempo_dum`; não há mais DUM artificial quando DUM e idade gestacional não existem.
- **Coorte correta**: cada gestação pertence ao quadrimestre em que ocorre o 42º dia do puerpério; códigos CIAP/CID de interrupção são aplicados dentro do episódio gestacional.
- **Práticas clínicas**: A–F e I–K são contadas apenas em suas janelas; E começa após a primeira consulta; F começa na 20ª semana; I usa consultas reais no puerpério.
- **Exames G/H**: sífilis, HIV, hepatites B e C passam a ser apurados por códigos SIGTAP em `tb_fat_atd_ind_exames` e procedimentos avaliados, sem inferência pelo número de consultas.
- **Cobertura DW**: procedimentos individualizados (MIP) complementam os registros de pressão arterial, exames e saúde bucal.
- **Integridade nominal**: exige nome, nascimento e CPF ou CNS; MICI/MICDT e distrito deixam de receber valores fictícios.
- **Busca ativa**: seletor de colunas e tabela nominal usam as mesmas chaves; a produção deixa de exibir coorte simulada quando ainda não há snapshot C3.
- **Validação automatizada**: testes cobrem as 11 práticas, janelas clínicas, exceção eAP e o formato da consulta que gerou `SQLSTATE[57014]`.

---

## [v1.16.1] - 19/09/2026

### 🔧 Compatibilidade Dinâmica com DW e-SUS PEC (Colunas e Tabelas)
- **Correção SQLSTATE[42P01]**: `relation "tb_dim_cid10" does not exist` — tabela oficial é `tb_dim_cid` (PK `co_seq_dim_cid`, coluna `nu_cid`).
- **Correção SQLSTATE[42703]**: `column a.nu_idade_gestacional does not exist` — coluna oficial é `nu_idade_gestacional_semanas`.
- **Detecção Dinâmica de DUM**: `dt_ultima_menstruacao` (legado) vs `co_dim_tempo_dum` (FK para `tb_dim_tempo_dum` no DW v8.7+).
- **Detecção Dinâmica de PA**: `nu_pressao_sistolica`/`nu_pressao_diastolica` (legado) vs `nu_medicao_pressao_sistolica`/`nu_medicao_pressao_diastolica` (v8.7+).
- **Fallback Seguro**: Se colunas não existirem no PEC, a query omite os campos/JOINs e filtra gestantes pelos critérios disponíveis.

---

## [v1.16.0] - 19/09/2026

### 🤰 Módulo C3 - Cuidado na Gestação e Puerpério na APS (Componente III)
- **Implementação Oficial Completa**: Conforme a **Nota Metodológica C3** (SAPS/MS), **NT 06/2025**, **NT 08/2026** (Componente III - Qualidade) e modelo DW e-SUS PEC (UFSC v8.7.0).
- **Peso e Pontuação Oficial**:
  - Peso **2.0** no Componente III - Qualidade com pontuação máxima de até **2,00 pontos** (Quadro 2 da NT 08/2026).
  - Conceito Regular: `≤ 25%` (0,50 pt) · Conceito Suficiente: `> 25% e ≤ 50%` (1,00 pt) · Conceito Bom: `> 50% e ≤ 75%` (1,50 pt) · Conceito Ótimo: `> 75% e ≤ 100%` (2,00 pts).
- **As 11 Boas Práticas Clínicas (100 Pontos Totais)**:
  - **Prática A (10 pts)**: Captação precoce da gestante com primeira consulta pré-natal (médico ou enfermeiro) realizada até a 12ª semana de gestação (≤ 12 sem).
  - **Prática B (9 pts)**: Mínimo de 7 consultas pré-natais realizadas por médico ou enfermeiro durante a gestação.
  - **Prática C (9 pts)**: Mínimo de 7 registros de aferição de pressão arterial ao longo do pré-natal.
  - **Prática D (9 pts)**: Mínimo de 7 registros de avaliação antropométrica simultânea (peso e altura aferidos no mesmo dia).
  - **Prática E (9 pts)**: Mínimo de 3 visitas domiciliares realizadas pelo ACS/TACS após o início do pré-natal (*equipes eAP tipo 76 pontuam integralmente 9 pts*).
  - **Prática F (9 pts)**: Administração de pelo menos 1 dose da vacina dTpa a partir da 20ª semana gestacional.
  - **Prática G (9 pts)**: Realização ou avaliação de exames do 1º trimestre (Sífilis, HIV, Hepatite B e Hepatite C) até a 13ª semana gestacional.
  - **Prática H (9 pts)**: Realização ou avaliação de exames do 3º trimestre (Sífilis e HIV) a partir da 28ª semana gestacional.
  - **Prática I (9 pts)**: Realização de consulta médica ou de enfermagem no período puerperal (até 42 dias pós-parto).
  - **Prática J (9 pts)**: Realização de visita domiciliar pelo ACS/TACS no puerpério até o 42º dia (*equipes eAP tipo 76 pontuam integralmente 9 pts*).
  - **Prática K (9 pts)**: Pelo menos 1 atendimento em saúde bucal com Cirurgião-Dentista (CBO 2232) ou TSB durante o período gestacional.
- **Definição Estrita da Coorte Avaliada**:
  - A pessoa gestante entra na coorte avaliada do quadrimestre quando o **42º dia de puerpério** (fim do puerpério) ocorre nos meses daquele quadrimestre (item 4.1.1 da Nota Metodológica C3).
- **Busca Ativa & Coorte Nominal**:
  - Visualização de gestantes ativas (semanas 1 a 42), puérperas recentes (até 42 dias pós-parto) e coorte avaliada.
  - Seletor de 22 colunas customizáveis com persistência de preferências.
  - Paginação rápida, ordenação por qualquer coluna e busca textual por Nome, CPF, CNS, Equipe, CNES e Telefone.
  - **Modal de Busca Avançada C3**: Filtros por Equipe, Microárea, Nome, CPF, CNS, Status Clínico (Gestante / Puérpera / Encerrada), Idade Gestacional, Trimestre e metas individuais das 11 práticas clínicas.
  - **Modal de Auditoria Clínica Individual**: Ficha completa da gestante com dados cadastrais, vínculo territorial, histórico da gestação (DUM, DPP, desfecho, fim do puerpério) e checklist minucioso do cumprimento das 11 práticas oficiais.
- **Integração com o Processamento de Dados**:
  - Adicionado escopo `--scope=c3` ao comando `php artisan esus:process-data` e botão dedicado "Processar C3 & Lista Gestantes" em Configurações.
  - Snapshot de coorte em `c3_cohort_snapshots` e registros nominais em `c3_nominal_pregnancies`.

---

## [v1.15.0] - 18/09/2026

### 👶 Refinamento da Busca Avançada C2 e Coorte de 0 a 24 Meses (7 Quadrimestres)
- **Remoção de Filtros Mockados**: Removidos os campos "Distrito" e "Unidade" do Modal de Busca Avançada do Indicador C2, preservando apenas o filtro de "Equipe" (com lista real de equipes homologadas eSF) e "Microárea".
- **Seletor de Mês Customizado (`MM / YYYY`)**: Novo dropdown interativo fiel ao padrão visual do sistema com busca textual em tempo real, botão de limpeza (`X`), indicador retrátil (`⌄`), rolagem estilizada, destaque de seleção com fundo azul claro e **opção explícita de deixar em branco / nenhum** para não filtrar por mês.
- **Seletor de Opção de Mês**: Dropdown customizado com busca rápida, opções "Mês Selecionado e Próximos Meses" (cumulativo) e "Apenas Mês Selecionado" (estrito), e **opção de deixar em branco / nenhuma** com botão de limpeza.
- **Filtragem Precisa por Quadrimestre**: Possibilidade de isolar apenas o "Quadrimestre Atual" ou qualquer um dos quadrimestres da janela sem imposição de filtro mensal, permitindo consultar todas as crianças que completam 2 anos naquele período.
- **Filtro Avançado de Idade (Meses)**:
  - **Chips Rápidos**: Botões de um clique para faixas `0-6 meses`, `7-12 meses` e `13-24 meses`.
  - **Dropdown Multiselect ("Selecione os meses")**: Menu com checkbox de seleção geral ("Selecionar todos"), campo de pesquisa filtrável e checkboxes individuais para cada mês de `0 meses` até `24 meses`.
- **Expansão para 7 Quadrimestres (Atual + 6 Futuros)**:
  - Processamento no e-SUS PEC expandido para extrair tanto o quadrimestre vigente quanto os 6 quadrimestres futuros (horizonte de 24 meses).
  - Como a coorte oficial C2 é definida por crianças que completam 2 anos de idade no quadrimestre, a busca em 7 quadrimestres cobre de forma completa e contínua todas as crianças de **0 a 24 meses de vida** do município.
  - Persistência e consultas nominais atualizadas para agregar a coorte expandida com status clínico e métricas de boas práticas infantis.

---

## [v1.14.2] - 18/09/2026

### 🎯 Centralização do Processamento de Dados
- **Remoção de Sincronização Isolada no C2**: O botão pontual "Sincronizar PEC" foi removido da visualização do Indicador C2 para manter uma arquitetura coesa e centralizada.
- **Módulo Unificado de Processamento**: Todas as extrações, consolidações e atualizações da coorte nominal e indicadores passam a ser disparadas exclusivamente pelo submódulo **Processamento de Dados** (`/configuracoes/processamento-dados`).
- **Botão Dedicado no Painel de Processamento**: O botão `Processar C2 & Lista Nominal` permite processar especificamente a coorte de crianças e alimentar a tabela nominal sem necessidade de reprocessar todos os cadastros.
- **Foco Analítico na Busca Ativa**: A aba de Busca Ativa permanece limpa e focada em auditoria, filtros, navegação e prontuário das crianças, exibindo a procedência dos dados (Base Real vs Demonstração).

---

## [v1.14.1] - 18/09/2026

### 🛡️ Compatibilidade Dinâmica de Colunas DW PEC no Indicador C2
- **Prevenção de Erro SQLSTATE[42703]**: Implementada detecção dinâmica das colunas físicas disponíveis em `tb_acomp_cidadaos_vinculados` via catálogo do PostgreSQL (`information_schema.columns`).
- **Eliminação de Colunas Inexistentes**: Removida a dependência estrita do campo `no_mae_cidadao`, que não faz parte da tabela de acompanhamento no e-SUS PEC, evitando a quebra da rotina de extração.
- **Tratamento Resiliente de Metadados Territoriais**: CNES, Unidade de Saúde, Microárea e Raça/Cor agora utilizam detecção adaptativa e fallbacks automáticos baseados no cadastro oficial das 19 equipes eSF do município.
- **Transações Seguras**: Garantida a execução atômica do processamento sem interrupção de transações no PostgreSQL da VPS.

---

## [v1.14.0] - 18/09/2026

### 👶 Conexão Nominal Real do C2 ao DW e-SUS PEC
- Substituição da amostragem demonstrativa por integração de dados reais extraídos diretamente do banco e-SUS PEC (PostgreSQL).
- **Tabela Local `c2_nominal_children`**: Armazenamento atômico dos registros nominais da coorte de crianças no MySQL, permitindo buscas instantâneas, filtros avançados e cálculos estatísticos sem onerar o banco de produção do PEC.
- **Campos Nominais Reais Extraídos**: Nome completo do cidadão, Nome da Mãe, CPF, CNS, data de nascimento, idade em meses, raça/cor, microárea, CNES da Unidade, INE da equipe e profissional responsável.
- **Sincronização Sob Demanda**: Novo botão "Sincronizar PEC" na aba Busca Ativa e integração com o processamento do indicador (`php artisan esus:process-data --scope=c2`), permitindo que gestores e coordenadores atualizem a base nominal com 1 clique.
- **Identificação de Procedência dos Dados**: Badge explicativo no cabeçalho sinalizando se a lista nominal exibida é proveniente da **Base Real e-SUS PEC** (com total de registros carregados) ou se está em modo de **Demonstração**.
- **Cálculo Real de KPIs**: O banner de síntese superior e os indicadores de cumprimento das 5 boas práticas (A, B, C, D, E) refletem os dados reais extraídos para o ano/quadrimestre selecionado.

---

## [v1.13.0] - 18/09/2026

### 👶 Busca Ativa e Boas Práticas Infantis no Indicador C2
- Refinamento completo da aba de Busca Ativa do Indicador C2 (Desenvolvimento Infantil), com banner de dados gerais, lista nominal da coorte com proteção LGPD, colunas personalizáveis, modal de busca avançada e auditoria clínica individual.
- **Banner de Síntese**: Card do mês (`2026 / M9`), indicador do denominador da coorte (`1.016` crianças) e acompanhamento das 5 boas práticas clínicas: (A) Consulta até 30º dia de vida: `775 (76.28%)`, (B) 9 Consultas de Puericultura: `610 (60.04%)`, (C) Peso e Altura Simultâneos: `477 (46.95%)`, (D) Visitas Domiciliares do ACS: `772 (75.98%)`, (E) Esquema Vacinal Completo: `374 (36.81%)`.
- **Filtros Rápidos no Topo**: Busca imediata por CNS, CPF, Nome da Criança, CNES da Unidade, INE da Equipe e seletor de paginação (10, 15, 30, 50 ou 100 itens).
- **Personalização de Colunas**: Dropdown interativo `Colunas visíveis: X itens selecionados ⌄` com seleção por checkbox de 19 colunas e botões para selecionar todas ou restaurar padrão.
- **Privacidade e LGPD**: Mascaramento visual de CNS e CPF com botões Alpine.js para desmascaramento instantâneo e cópia de 1 clique para a área de transferência.
- **Modal de Busca Avançada**: Filtros territoriais (Distrito, UBS, eSF, Microárea), do cidadão (Nome, CPF, CNS, Nome da Mãe), profissional ACS (CNS e Nome), Raça/Cor, Faixa etária em meses (com chips 0-6m, 7-12m, 13-24m) e botões toggle SIM/NÃO para MICI, MICDT, Acompanhada e Práticas A a E.
- **Prontuário e Auditoria Clínica**: Modal de detalhes com perfil completo da criança, vínculo da UBS/eSF/ACS e avaliação de cumprimento das metas preconizadas pela Portaria 3.493/2024.

---

## [v1.12.0] - 18/09/2026

### 🧭 Inventário seguro do DW PEC
- Novo comando `php artisan esus:inspect-schema` para inventariar o esquema disponível na VPS antes da criação da camada analítica local.
- A inspeção roda em transação PostgreSQL explicitamente somente leitura e coleta somente metadados: tabelas, colunas, tipos, índices e estimativas do catálogo.
- O relatório JSON é gravado em `storage/app/private/` e não contém linhas clínicas, CPF, CNS, nomes ou outros dados de cidadãos.
- A arquitetura do MySQL foi planejada em quatro camadas: referências, entidades canônicas, eventos clínicos e produtos analíticos.
- O mapeamento prevê evidências por cidadão e por boa prática para C1 a C7, além de vínculo, Saúde Bucal, e-Multi, vigilância e vacinação infantil.

---

## [v1.11.0] - 18/09/2026

### 🧭 Revisão normativa do C1 pelo DW PEC
- O numerador considera exclusivamente os tipos de atendimento `1` (consulta agendada programada/cuidado continuado) e `2` (consulta agendada).
- O denominador considera somente `1`, `2`, `4` (escuta inicial/orientação), `5` (consulta no dia) e `6` (urgência).
- A extração exige um dos sete CBOs oficiais, CNS do profissional, data de nascimento e CPF ou CNS válido do cidadão. O filtro amplo por prefixo e o fallback sem CBO foram removidos.
- Competências ainda não monitoradas aparecem como **sem dados**. A prévia local usa apenas os meses disponíveis; a avaliação quadrimestral definitiva continua sendo a média de M1 a M4.
- Falhas na leitura preservam o último snapshot válido. Resultados simulados ou calculados por versões anteriores ficam ocultos até uma extração C1 válida.
- Limitação conhecida: a tabela fato pública expõe data de nascimento e CPF/CNS, mas não o nome do cidadão; a validação do nome depende de confirmar uma fonte estável no esquema da VPS.

---

## [v1.10.0] - 18/09/2026

### 🚀 Prévia C2 dos quatro meses
- Todas as crianças da coorte M1–M4 passam a ter pontuação prévia baseada nas práticas registradas no DW PEC até a data da extração, inclusive as que completarão dois anos nos meses futuros.
- Os cards mensais mostram a pontuação e identificam meses em andamento ou futuros como prévia. A média quadrimestral local inclui todos os meses que possuem crianças na coorte.
- O painel distingue a coorte total da quantidade de crianças que já completaram dois anos. A prévia serve para acompanhar o cuidado e não substitui a nota oficial do Siaps.
- Consultas de fatos clínicos limitadas à data da extração; a sincronização continua somente de leitura no PEC e grava apenas agregados locais.

---

## [v1.9.0] - 18/09/2026

### 🚀 Indicador C2 pelo DW PEC
- Extração somente de leitura para calcular as cinco boas práticas do cuidado no desenvolvimento infantil e consolidar resultados por equipe e mês.
- Coorte quadrimestral inclui todas as crianças vinculadas que completam dois anos entre o primeiro e o último dia dos quatro meses, inclusive aniversários futuros quando o período ainda está em andamento.
- O painel distingue o total da coorte do número de crianças que já completaram dois anos e foram avaliadas. Meses futuros não recebem pontuação antecipada.
- Resultados antigos simulados ficam ocultos até nova extração. O cálculo local permanece preliminar e pode divergir do Siaps por RNDS e vínculo histórico.
- Nova tabela de snapshots agregados da coorte, sem persistir nome, CPF ou CNS das crianças.

---

## [v1.8.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Filtragem Estrita de Equipes Elegíveis no Indicador C1 (eSF Tipo 70 e eAP Tipo 76):** Conformidade rigorosa com a Nota Metodológica C1 - Mais Acesso e NT 08/2026-DEAPS/SAPS/MS. As únicas equipes que competem e integram o C1 são exclusivamente eSF (40h) e eAP (20h/30h).
- **Isolamento de Equipes Não Elegíveis:** Equipes de Saúde Bucal (eSB - Tipo 71, que pertencem aos indicadores B1 a B6), equipes Multiprofissionais (eMulti - Tipo 72, indicadores M1 e M2) e Atenção Domiciliar (EMAD Tipo 22 e EMAP Tipo 23) foram completamente isoladas e descartadas do Indicador C1.
- **Validação das 19 Equipes Homologadas de Teotônio Vilela/AL:** Integração com o arquivo oficial do CNES (`XmlParaESUS31_270915.xml`), mapeando exatamente as 19 equipes eSF do município e expurgando as 19 eSB, 2 eMulti, EMAD I e EMAP I.
- **Filtro Rigoroso dos 7 CBOs Habilitados na Produção Clínica:** JOIN e filtro estrito na `tb_dim_cbo` da `tb_fat_atendimento_individual` para considerar apenas atendimentos médicos (`2251-42`, `2251-70`, `2251-30`, `2251-25`, `2252-50`) e de enfermagem (`2235-65`, `2235-05`), impedindo que atendimentos de dentistas ou multiprofissionais afetem o cálculo do C1.
- **Expurgo e Sanitização de Snapshots:** Criação do método `purgeInvalidC1Snapshots()` e eliminação de registros de sistema como `"SEM EQUIPE"`, `"INE NÃO ENCONTRADO"` e INEs inválidos.

---

## [v1.7.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Correção da Importação de Equipes em Produção (e-SUS PEC DW):** Resolução definitiva do erro `SQLSTATE[42703]` (`column tp_equipe does not exist in tb_dim_equipe`) através de detecção dinâmica de colunas via `information_schema` e extração combinada com a produção clínica de `tb_fat_atendimento_individual`, garantindo que 100% das equipes com atendimentos sejam importadas e listadas no Indicador C1.
- **Processamento Seletivo por Indicador (C1 vs Geral):** Criação da função de processamento focado no Indicador C1 (Mais Acesso), que processa equipes, competências e atendimentos em menos de 1 segundo (pulando os mais de 800 mil cadastros individuais e domiciliares), além da opção de processamento geral completo.
- **Rotina Agendada Sempre Completa:** O agendamento diário no servidor via scheduler/cron (às 03:30) executa sempre o modo completo (`--scope=all`), garantindo a integridade contínua de toda a base de dados municipal.
- **Inversão da Ordem da Legenda do C1:** Ordem de apresentação oficial atualizada para: **Regular, Suficiente, Bom e Ótimo**.
- **Nova Paleta Estrita de Cores:** Atribuição das cores oficiais: **Vermelho** (Regular: $\le 10\%$ ou $> 70\%$), **Amarelo** (Suficiente: $> 10\%$ e $\le 30\%$), **Verde** (Bom: $> 30\%$ e $\le 50\%$) e **Azul** (Ótimo: $> 50\%$ e $\le 70\%$) na régua de faixas, placares, cards mensais, tabela de equipes e Quadro 2.

---

## [v1.6.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Processamento Real de Dados do e-SUS PEC:** Substituição definitiva de dados mockados por pipeline real com barra de progresso em tempo real e painel detalhado de status/contagem das 7 tabelas do Data Warehouse do e-SUS PEC (`tb_dim_equipe`, `tb_dim_tempo`, `tb_dim_cbo`, `tb_dim_tipo_atendimento`, `tb_fat_atendimento_individual`, `tb_fat_cad_individual`, `tb_fat_cad_domiciliar`).
- **Acompanhamento Mensal do Indicador C1 (Mais Acesso à APS):** Monitoramento individual dos 4 meses do quadrimestre (Mês 1, Mês 2, Mês 3 e Mês 4) da relação entre oferta de demanda programada e espontânea.
- **Avaliação Quadrimestral e Componente III (NT 08/2026-DEAPS/SAPS/MS):** Cálculo oficial da média aritmética simples dos 4 meses (`(M1 + M2 + M3 + M4) / 4`) com classificação oficial (Ótimo, Bom, Suficiente e Regular) e conversão em pontos do Componente III - Qualidade (Quadro 2: 1,00 pt, 0,75 pt, 0,50 pt ou 0,25 pt com Peso 1,0).

### ✨ Novas Funcionalidades
- **Barra de Progresso & Diagnóstico de Tabelas em Configurações > Processar Dados:**
  - Barra de progresso interativa com percentual e descrição da etapa em execução.
  - Tabela de auditoria com status (`Processado`, `Atenção`, `Pendente`), quantidade de registros e diagnóstico de cada tabela do PEC.
- **Painel Dedicado para o Indicador C1 · Mais Acesso:**
  - Card de Síntese Quadrimestral com fórmula oficial e pontuação no Componente III.
  - Grid com 4 cards de acompanhamento mensal mostrando percentuais, atendimentos programados vs espontâneos e pontos parciais por mês.
  - Tabela de equipes enriquecida com colunas para Mês 1, Mês 2, Mês 3, Mês 4, Média Quadrimestral, Conceito e Pontos do Componente III.
  - Diagnóstico de equilíbrio da agenda na aba de Oportunidades (identificando equipes com sobrecarga $> 70\%$ ou desestruturação $< 30\%$).
  - Caderno metodológico atualizado com referências explícitas à NT 08/2026-DEAPS/SAPS/MS e Quadro 2.

---

## [v1.5.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Módulo Saúde da Família (Indicadores C1 ao C7):** Implementação completa do módulo de monitoramento clínico dos 7 indicadores da Atenção Primária à Saúde conforme as notas metodológicas oficiais da Portaria GM/MS nº 3.493/2024.
- **Painel Municipal Consolidado:** Visão executiva de desempenho global com métricas de cada indicador, médias ponderadas e status de homologação de equipes.
- **Busca Ativa & Oportunidades:** Listagem nominal detalhada de cidadãos com pendências de cuidados para busca ativa precoce pelas equipes de Saúde da Família e Atenção Primária.

### ✨ Novas Funcionalidades
- **Navegação Integrada na Barra Lateral:** Acordeão dedicado "Saúde da Família" com acesso rápido à Visão Geral e a cada indicador individual (C1 a C7).
- **Indicadores Contemplados:**
  - **C1 · Mais Acesso à APS:** Percentual de acesso de demanda programada com faixa de meta ideal (30% a 70%).
  - **C2 · Cuidado no Desenvolvimento Infantil:** Monitoramento de 5 boas práticas clínicas até 2 anos de idade (captação até 30 dias, 9 consultas, 9 antropometrias, 2 visitas do ACS e vacinação completa).
  - **C3 · Cuidado na Gestação e Puerpério:** 11 boas práticas com captação no 1º trimestre, 7 consultas, PA, peso/altura, visitas domiciliares, vacina dTpa, testes rápidos e saúde bucal.
  - **C4 · Cuidado da Pessoa com Diabetes:** 6 boas práticas com consultas semestrais, aferição de PA, antropometria, visitas de ACS, hemoglobina glicada anual e exame dos pés.
  - **C5 · Cuidado da Pessoa com Hipertensão:** 4 boas práticas com consultas semestrais, aferição de PA, antropometria e visitas de ACS.
  - **C6 · Cuidado Integral à Pessoa Idosa:** 4 boas práticas para população ≥ 60 anos com consulta anual, antropometria, visitas domiciliares de ACS e vacinação contra influenza.
  - **C7 · Cuidado na Prevenção do Câncer da Mulher:** Métrica ponderada cobrindo exame citopatológico/HPV, vacinação contra HPV em meninas, saúde sexual/reprodutiva e mamografia de rastreamento.
- **Detalhamento por Equipe:** Tabela comparativa com INE, tipo de equipe (eSF/eAP), numerador, denominador, percentual alcançado e classificação oficial (Ótimo, Bom, Suficiente e Regular).
- **Caderno Metodológico Oficial:** Ficha técnica completa de cada indicador com fórmulas, CBOs habilitados e modelos de informação e-SUS (MIAI, MIP, MIVDT, RIA/RNDS).

---

## [v1.4.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Sistema de Notificação de Novidades:** Modal automático exibido no primeiro login do usuário a cada nova versão implantada no sistema, permitindo que a equipe gestora fique sempre informada das melhorias.
- **Módulo de Novidades da Versão:** Submódulo interativo dentro de "Ajuda" (`/ajuda/novidades`) com linha do tempo de todas as atualizações, filtros de categorias e histórico completo.
- **Registro Centralizado de Versões (`versoes.md`):** Documento oficial para controle de ciclo de vida e auditoria técnica.

### ✨ Novas Funcionalidades
- Adicionado campo `last_seen_version` na tabela de usuários para controle de leitura persistente entre múltiplos acessos e navegadores.
- Adicionado componente Livewire `WhatsNewModal` acoplado ao layout da aplicação para notificação contextual e não intrusiva.
- Adicionada aba "Novidades da Versão" no cabeçalho do módulo de Ajuda.
- Adicionado indicador visual de versão ativa (`v1.4.0`) no rodapé da barra lateral de navegação.

### 🛡️ Testes e Qualidade
- Criados testes automatizados em `tests/Feature/VersionControlTest.php` cobrindo o serviço de versões, rotas e fluxo de persistência de confirmação do modal.

---

## [v1.3.0] - 18/09/2026

### 🚀 Destaques da Versão
- **Módulo de Ajuda e Guia de Preenchimento:** Implementação do módulo oficial de suporte técnico baseado nas diretrizes da Secretaria de Atenção Primária à Saúde (SAPS/MS), Portaria GM/MS nº 3.493/2024 e Nota Técnica nº 30/2025.

### ✨ Novas Funcionalidades
- **Guia Interativo de Preenchimento (`/ajuda/guia-preenchimento`):**
  - **Saúde da Família (eSF / eAP):** Diretrizes para os indicadores C1 a C7 (Mais Acesso à APS, Desenvolvimento Infantil, Pré-natal, Hipertensão, Diabetes, Citopatológico e Cobertura Vacinal).
  - **Saúde Bucal (eSB):** Diretrizes para os indicadores odontológicos B1 a B6 (Primeira Consulta Odontológica Programática, Tratamentos Concluídos, Escovação Supervisionada, D2 e restaurações atraumáticas).
  - **Equipes eMulti:** Diretrizes para os indicadores M1 e M2 (Acompanhamento Individual/Compartilhado e Matriciamento).
  - **Cadastros Estruturantes:** Regras para MICI (Cadastro Individual) e MICDT (Cadastro Domiciliar/Territorial) com vigência de 24 meses.
- **Acesso Rápido ao Portal Oficial:** Botões de link direto para a base documental oficial do Ministério da Saúde (`https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/`).
- **Regras de Ouro:** Painel informativo com melhores práticas operacionais para evitar perda de produção e inconsistências de CNES/INE.

### 🔧 Melhorias e Correções
- Publicação física dos assets do Livewire (`public/livewire/livewire.min.js`) e configuração de proxy reverso HTTPS para compatibilidade plena em navegadores modernos (Microsoft Edge, Google Chrome, Safari).

---

## [v1.2.0] - 17/09/2026

### 🚀 Destaques da Versão
- **Módulo Completo de Configurações:** Centralização administrativa da plataforma dividida em 6 submódulos essenciais com navegação por acordeão na barra lateral e abas horizontais no topo.

### ✨ Novas Funcionalidades
- **Gerenciamento de Usuários (`/configuracoes/usuarios`):** CRUD completo de operadores com modais reativos, criptografia Bcrypt, busca instantânea e proteção contra autoexclusão.
- **Parâmetros do Município (`/configuracoes/municipio`):** Edição de Nome, Código IBGE e CNES da Sede, além de upload de logotipo municipal com persistência em disco.
- **Log de Auditoria (`/configuracoes/logs-auditoria`):** Histórico paginado de todas as sincronizações do sistema com status de execução, medição de duração e inspeção de erros.
- **Conexão com e-SUS PEC (`/configuracoes/conexao-esus`):** Teste de conectividade em tempo real com o banco de dados PostgreSQL do e-SUS, diagnóstico de tabelas essenciais e aferição de latência em milissegundos.
- **Processamento de Dados (`/configuracoes/processar-dados`):** Disparo manual de sincronização de snapshot (`esus:sync-snapshot`) diretamente pela interface com feedback ao vivo.
- **Importação de CNES / XML (`/configuracoes/importar-cnes-xml`):** Validação segura de XML via `XMLReader`, verificação cruzada de código IBGE e contagem automática de equipes homologadas (eSF, eSB, eMulti).

---

## [v1.1.0] - 16/09/2026

### 🚀 Destaques da Versão
- **Integração Nativa com o e-SUS APS PEC:** Conector direto com o banco de dados PostgreSQL do Prontuário Eletrônico do Cidadão para consolidação automática de produção municipal.

### ✨ Novas Funcionalidades
- **Comando de Sincronização (`php artisan esus:sync-snapshot`):** Extração e cálculo de dados de produção agregada por quadrimestre e competência.
- **Suporte a Equipes eMulti:** Inclusão das equipes multiprofissionais da atenção primária e acompanhamento dos indicadores M1 e M2.
- **Contabilização de Cadastros:** Leitura consolidada dos modelos de informação MICI e MICDT.
- **Registro de Execuções (`sync_logs`):** Rastreabilidade de cada rotina de sincronização com timestamp e status.

---

## [v1.0.0] - 15/09/2026

### 🚀 Destaques da Versão
- **Lançamento Oficial da Plataforma Monitora Fácil:** Sistema de inteligência e monitoramento contínuo para a Atenção Primária à Saúde (APS) de municípios brasileiros.

### ✨ Funcionalidades Iniciais
- **Painel Executivo Geral (`/dashboard`):** Visão consolidada dos indicadores da Portaria de Cofinanciamento Federal da Atenção Primária.
- **Indicadores de Qualidade do Cuidado:**
  - Equipes de Saúde da Família e APS (C1 a C7).
  - Equipes de Saúde Bucal (B1 a B6).
- **Projeções e Cenários Financeiros:** Estimativa de repasse de incentivo de qualidade por faixa de desempenho e simulação de impacto financeiro municipal.
- **Autenticação Segura:** Login administrativo com controle de sessão e layout corporativo com paleta verde-esmeralda executiva.
