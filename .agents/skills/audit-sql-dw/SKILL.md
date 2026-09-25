---
name: audit-sql-dw
description: >
  Auditoria estrita de performance, otimização de planos de execução e segurança
  de consultas SQL sobre a réplica de grande porte do PostgreSQL do e-SUS PEC
  no Monitora Fácil. Focado nos serviços de extração DW (C*DwService,
  FamilyHealthService, CvatNominalDwService). Previne Sequential Scans (Seq Scan),
  explosão de cardinalidade 1:N, bloqueios de leitura e estouro de memória PHP.
---

# Auditoria e Otimização de SQL para e-SUS PEC DW

Esta skill estabelece o protocolo de engenharia de dados e auditoria de consultas SQL executadas contra o banco de dados réplica do **e-SUS PEC (PostgreSQL)**, cujas tabelas fato (`tb_fat_*`) acumulam centenas de milhares a dezenas de milhões de registros.

---

## 1. Gatilhos de Ativação

Ative esta skill quando a tarefa envolver:
- Criação ou modificação de queries SQL dentro de `app/Services/C*DwService.php`, `FamilyHealthService.php` ou `CvatNominalDwService.php`.
- Diagnóstico de lentidão, timeout de requisições ou estouro de memória (`Allowed memory size of ... bytes exhausted`) durante a sincronização/processamento.
- Verificação de índices e planos de execução (`EXPLAIN ANALYZE`) no PostgreSQL do PEC.
- Revisão de queries que interajam com tabelas volumosas: `tb_fat_atendimento_individual`, `tb_fat_atd_ind_procedimentos`, `tb_fat_atd_ind_problemas`, `tb_fat_visita_domiciliar`, `tb_fat_vacinacao`.

---

## 2. As 4 Regras de Ouro de Performance do DW PEC

### Regra 1: Preferir `WHERE EXISTS` a `JOIN` para Checagens Históricas
- **Problema:** Fazer `JOIN` de uma lista de cidadãos com `tb_fat_atendimento_individual` e `tb_fat_atd_ind_procedimentos` gera um produto cartesiano massivo (1 cidadão possui dezenas de atendimentos, cada um com múltiplos procedimentos). Isso multiplica as linhas retornadas, consumindo gigabytes de memória no PHP e saturando a rede.
- **Diretriz:** Se o objetivo for apenas verificar a ocorrência de um procedimento, exame ou consulta dentro da janela, utilize `WHERE EXISTS (SELECT 1 FROM ...)`, ou estruture queries direcionadas com agregação (`MIN(dt_registro)`, `MAX(dt_registro)`).

### Regra 2: Filtros de Partição e Data Mandatórios com `tb_dim_tempo`
- **Problema:** A tabela `tb_fat_atendimento_individual` no e-SUS PEC é historicamente volumosa e particionada ou indexada por tempo via `co_dim_tempo`. Fazer varreduras abertas sem restringir o tempo força o PostgreSQL a ler partições de anos anteriores desnecessariamente.
- **Diretriz:** Toda query em fatos de atendimento individual deve conter junção com `tb_dim_tempo` e filtragem explícita pelo intervalo de datas (`dt_registro BETWEEN ? AND ?`) ou pela competência (`nu_ano_quadrimestre = ?` ou `nu_ano = ?`).

### Regra 3: Prevenção Radical de Sequential Scan (Seq Scan)
- **Problema:** Aplicar funções no lado da coluna do `WHERE` anula o uso de índices B-Tree existentes.
  - ❌ `WHERE DATE(t.dt_registro) >= '2026-01-01'` (anula o índice em `dt_registro`).
  - ❌ `WHERE SUBSTRING(c.nu_cpf, 1, 3) = '123'` (anula o índice no CPF).
  - ❌ `WHERE LOWER(ci.no_cidadao) LIKE '%JOAO%'` (Seq scan completo na tabela nominal).
- **Diretriz:** Mantenha a coluna indexada limpa de funções. Faça o tratamento no parâmetro ou no binding do PHP:
  - ✅ `WHERE t.dt_registro >= ?` (com binding `'2026-01-01'`).
  - ✅ `WHERE c.nu_cpf = ?` (com formatação tratada previamente no PHP).
  - Use `LIKE 'prefixo%'` apenas quando houver índice e for estritamente necessário (ex: `nu_cid LIKE 'E11%'`).

### Regra 4: Leitura Segura (`READ ONLY`) e Paginação em Lotes (`Chunking`)
- **Problema:** Tentar carregar 20.000 cidadãos com todos os seus exames de uma só vez estoura a memória do processo PHP e bloqueia conexões do pool.
- **Diretriz:**
  - A conexão com o PEC é estritamente de leitura.
  - IDs de cidadãos devem ser fatiados em lotes de no máximo 100 a 500 registros (`array_chunk($citizenIds, 100)`).
  - Evite `SELECT *`; selecione estritamente as colunas necessárias para o cálculo (`co_fat_cidadao_pec`, `dt_registro`, `co_dim_tempo`, etc.).

---

## 3. Exemplos Comparativos: Ruim vs Otimizado

### Exemplo 1: Verificação de Hemoglobina Glicada no Último Ano

#### ❌ Abordagem Inadequada (Explosão de Cardinalidade e Seq Scan)
```sql
-- RUIM: Multiplica linhas por cada procedimento de cada atendimento da vida do paciente,
-- usa DATE() anulando índice, e consome memória excessiva no PHP.
SELECT c.co_seq_fat_cidadao_pec,
       fai.co_seq_fat_atd_ind,
       pr.co_proced,
       t.dt_registro
FROM tb_fat_cidadao_pec c
JOIN tb_fat_atendimento_individual fai ON fai.co_fat_cidadao_pec = c.co_seq_fat_cidadao_pec
JOIN tb_fat_atd_ind_procedimentos faip ON faip.co_fat_atd_ind = fai.co_seq_fat_atd_ind
JOIN tb_dim_procedimento pr ON pr.co_seq_dim_procedimento = faip.co_dim_procedimento
JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = fai.co_dim_tempo
WHERE c.co_seq_fat_cidadao_pec IN (?, ?, ?)
  AND DATE(t.dt_registro) >= '2025-09-01'
  AND pr.co_proced IN ('0202010503', 'ABEX008');
```

#### ✅ Abordagem Otimizada (Chunking + EXISTS / Agrupamento Direto)
```sql
-- OTIMIZADO: Chunk controlado no PHP, filtragem direta com índice em dt_registro e busca por IDs pré-carregados
SELECT fai.co_fat_cidadao_pec AS cidadao_id,
       MAX(t.dt_registro) AS ultima_coleta
FROM tb_fat_atendimento_individual fai
JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = fai.co_dim_tempo
WHERE fai.co_fat_cidadao_pec IN (?, ?, ?, ...) -- Lote de 100 IDs
  AND t.dt_registro >= ?                      -- Binding limpo de Carbon '2025-09-01'
  AND t.dt_registro <= ?                      -- Binding limpo de Carbon '2026-08-31'
  AND EXISTS (
      SELECT 1
      FROM tb_fat_atd_ind_procedimentos faip
      WHERE faip.co_fat_atd_ind = fai.co_seq_fat_atd_ind
        AND faip.co_dim_procedimento IN (?, ?) -- IDs sequenciais pré-resolvidos de tb_dim_procedimento
  )
GROUP BY fai.co_fat_cidadao_pec;
```

---

### Exemplo 2: Resolução Prévia de IDs Dimensionais

Em vez de fazer `JOIN tb_dim_ciap` repetidamente dentro de loops ou queries volumosas, resolva os IDs uma única vez na inicialização do serviço:

```php
// No C*DwService.php:
$ciapRows = $connection->select("
    SELECT co_seq_dim_ciap AS id, nu_ciap 
    FROM tb_dim_ciap 
    WHERE nu_ciap IN ('T89', 'T90')
");
$ciapIds = array_map(static fn ($r) => (int) $r->id, $ciapRows);

// Agora injete diretamente os inteiros $ciapIds nas queries de fato:
// WHERE p.co_dim_ciap IN (124, 125) -> O PostgreSQL usa índice numérico direto sem JOIN dimensional
```

---

## 4. Checklist de Auditoria para o Agente

Antes de salvar qualquer query SQL destinada ao e-SUS DW, verifique cada item:

1. [ ] **Sem `SELECT *`:** Apenas as colunas estritamente consumidas pelo serviço estão selecionadas.
2. [ ] **Sem funções em colunas no `WHERE`:** Verifique se não há `DATE(col)`, `LOWER(col)`, `TRIM(col)`, `COALESCE(col, ...)` no lado da comparação.
3. [ ] **Resolução Dimensional Prévia:** Chaves de dimensões pequenas (`tb_dim_tempo`, `tb_dim_ciap`, `tb_dim_cid`, `tb_dim_procedimento`, `tb_dim_cbo`) foram resolvidas previamente por ID inteiro?
4. [ ] **Filtro Temporal Obrigatório:** A consulta restringe explicitamente `dt_registro` ou `nu_ano_quadrimestre`?
5. [ ] **Prevenção de Explosão 1:N:** Para checagens booleanas ou data do último exame, foi utilizado `WHERE EXISTS` ou agregação com `MAX()` em vez de `JOIN` plano?
6. [ ] **Controle de Lote (Chunking):** A passagem de múltiplos IDs de cidadão ou equipe está protegida por fatias (`array_chunk(..., 100)`)?
7. [ ] **Leitura Isolada:** A operação não executa `INSERT`, `UPDATE`, `DELETE` ou `LOCK` na base réplica do PEC?
