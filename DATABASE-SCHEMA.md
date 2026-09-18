# Modelagem Relacional (MySQL 8.0 - Single-Tenant)

## Tabelas

### `users`
Usuários administradores do painel gerencial municipal.
* `id` (bigIncrements)
* `name` (string)
* `email` (string, unique)
* `password` (string)
* `timestamps`

### `settings`
Tabela chave-valor para configurações globais do município (White Label).
* `id` (bigIncrements)
* `key` (string, unique) - Ex: 'municipio_nome', 'municipio_ibge', 'logo_path'
* `value` (text, nullable)
* `timestamps`

### `consolidation_teams`
Snapshot das equipes homologadas consolidadas no ETL noturno.
* `id` (bigIncrements)
* `year` (integer) - Ex: 2026
* `quarter` (integer) - Ex: 3
* `type` (enum: 'esf', 'esaude_bucal', 'emulti')
* `total_active` (integer)
* `timestamps`
* UNIQUE KEY: `year, quarter, type` (Garante que só haja um registro por tipo no quadrimestre)

### `consolidation_registrations`
Snapshot dos cadastros de vínculo (MICI e MICDT).
* `id` (bigIncrements)
* `year` (integer)
* `quarter` (integer)
* `mici_updated_count` (integer)
* `mici_outdated_count` (integer)
* `micdt_updated_count` (integer)
* `micdt_outdated_count` (integer)
* `timestamps`
* UNIQUE KEY: `year, quarter`

### `sync_logs`
Histórico de execução do Job de integração com o e-SUS.
* `id` (bigIncrements)
* `status` (enum: 'success', 'failed', 'running')
* `started_at` (timestamp)
* `finished_at` (timestamp, nullable)
* `error_message` (text, nullable)
* `timestamps`