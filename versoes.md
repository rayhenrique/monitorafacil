# 📦 Histórico de Versões e Atualizações · Monitora Fácil

Documento oficial de versionamento semântico (`SemVer`) e notas de lançamento (*Release Notes*) da plataforma **Monitora Fácil · Gestão da Atenção Primária à Saúde**.

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
