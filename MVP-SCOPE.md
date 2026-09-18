# Declaração de Escopo (MVP) - Instância Municipal Única

## O que ESTÁ INCLUÍDO na Fase 1
1. **Instalação Isolada (Single-Tenant):** Banco de dados e aplicação exclusivos para um município.
2. **Personalização Dinâmica (Settings):** Logotipo e dados da prefeitura parametrizados via banco.
3. **Painel Gerencial (Visão do Secretário):** Filtro por Quadrimestres (ex: Q1, Q2, Q3 / 2026).
4. **Módulo Vínculo e Equipes:** Consolidado de equipes homologadas (CNES/INE) e totais de MICI/MICDT atualizados.
5. **Estimador Financeiro:** Cálculo baseado no enquadramento (Ótimo, Bom, Suficiente, Regular).
6. **ETL Noturno Automático:** Sincronização Scheduled via console que consulta o PostgreSQL e gera o snapshot local no MySQL.

## O que NÃO ESTÁ INCLUÍDO na Fase 1 (Postergado para Fases Seguintes)
* 🚫 Autenticação e painel para Agentes Comunitários (ACS) ou coordenadores de UBS individuais.
* 🚫 Processamento dos 7 indicadores individuais clínicos de saúde (pré-natal, hiperdia, citopatológico, etc).
* 🚫 Listas Nominais para busca ativa.
* 🚫 Módulo Saúde na Escola (PSE).

## Métricas de Sucesso Técnico do MVP
1. **Zero Impacto no PEC:** A rotina noturna não deve degradar a performance do PostgreSQL da prefeitura.
2. **Carregamento Instantâneo:** O carregamento da dashboard web deve ser imediato (< 500ms), consumindo apenas os totais pré-calculados do MySQL.