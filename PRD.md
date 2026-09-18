# Product Requirements Document (PRD) - Saúde Brasil 360 Monitor (Single-Tenant)

## 1. Visão Geral
O sistema é um painel gerencial focado no monitoramento dos indicadores da Atenção Primária à Saúde (APS) sob o novo modelo de cofinanciamento (Saúde Brasil 360). Esta aplicação Single-Tenant é instalada individualmente para cada município. O MVP tem o objetivo de consolidar diariamente os dados do banco do e-SUS PEC (PostgreSQL) para o banco da aplicação (MySQL), oferecendo ao gestor uma visão rápida de equipes homologadas, vínculo/acompanhamento e projeção financeira, sem sobrecarregar o servidor do e-SUS.

## 2. Perfis e Permissões (Roles)
* **Gestor / Admin:** Acesso total ao painel. Pode visualizar consolidações quadrimestrais, simulações financeiras e alterar configurações de personalização (White Label) na interface.
* *(Nota: Sendo Single-Tenant, não há necessidade de um Super Admin SaaS nesta fase).*

## 3. Requisitos Funcionais (Core MVP)
* **Módulo de Configuração (Settings):**
  * Gerenciamento de variáveis do município armazenadas em banco (Nome, Código IBGE, CNES da Sede, Caminho da Logo).
* **Módulo de Ingestão (ETL Noturno):**
  * Conexão estática configurada via `.env` (`pgsql_esus`) apontando para o servidor PostgreSQL da prefeitura.
  * Command agendado (Cron/Schedule) para rodar de madrugada.
  * Extração e contagem de Equipes ativas (eSF, eSB, e-Multi) pelo INE/CNES.
  * Consolidação do quantitativo de cadastros MICI e MICDT (atualizados nos últimos 24 meses vs desatualizados).
  * Gravação dos snapshots no MySQL local (indexados por ano e quadrimestre).
* **Módulo Dashboard Gestor:**
  * Filtro global de Ano e Quadrimestre (Q1, Q2, Q3).
  * Cards informativos de Equipes Homologadas.
  * Painel de Indicadores de Vínculo e Acompanhamento.
  * Simulador de Projeção Financeira baseado nos resultados alcançados (Ótimo, Bom, Suficiente, Regular).

## 4. Requisitos Não-Funcionais
* **Performance e Isolamento:** O painel web nunca consulta o PostgreSQL do e-SUS. Todas as leituras são feitas no MySQL local, que atua como Data Warehouse/Cache.
* **Segurança:** Conexão com o banco do e-SUS limitada apenas a um usuário com privilégios restritos de leitura (Read-Only).