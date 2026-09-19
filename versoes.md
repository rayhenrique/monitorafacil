# 📦 Histórico de Versões e Atualizações · Monitora Fácil

Documento oficial de versionamento semântico (`SemVer`) e notas de lançamento (*Release Notes*) da plataforma **Monitora Fácil · Gestão da Atenção Primária à Saúde**.

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
