---
name: new-indicator-calculator
description: >
  Scaffold padronizado e geração completa da cadeia de arquivos para um novo
  indicador de saúde da Atenção Primária à Saúde (APS) no Monitora Fácil
  (C1 a C7 ou novos indicadores). Executa o fluxo arquitetural estrito:
  Migrations, Models de Coorte e Nominal, Extração DW (C*DwService),
  Calculador de Práticas puras (C*PracticeCalculator), Busca Ativa Nominal
  (C*ActiveSearchService), Consolidação e Snapshots (C*SnapshotService) e
  Testes Unitários e de Feature automatizados.
---

# New Indicator Calculator

Esta skill guia o desenvolvimento, scaffold e implementação completa de ponta a ponta de novos indicadores do Componente de Desempenho da APS (como C1 a C7 ou novas regras do Ministério da Saúde) no **Monitora Fácil**.

---

## 1. Gatilhos de Ativação

Ative esta skill quando a tarefa envolver:
- Criação de um novo indicador clínico ou de processo (ex: `C8`, `C9`, novo indicador quadrimestral).
- Refatoração profunda ou reconstrução da cadeia completa de um indicador existente (`C1` a `C7`).
- Atualização metodológica que altere coorte, regras de pontuação de práticas ou estrutura nominal.

---

## 2. Pré-Requisitos e Regras Obrigatórias

1. **Consulta Normativa Obrigatória via RAG:**
   - Antes de escrever qualquer regra ou SQL, invoque o tool MCP `search_aps_rules` para recuperar a nota metodológica oficial, códigos CID-10, CIAP-2, procedimentos SIGTAP e CBOs homologados.
2. **Documentação do Banco de Dados:**
   - Sempre que criar migrations ou novas tabelas, atualize imediatamente o arquivo `DATABASE-SCHEMA.md`.
3. **Integridade de Dados (Zero Mock):**
   - Nunca gerar dados fictícios. Os dados devem ser 100% extraídos da réplica do PostgreSQL do e-SUS PEC DW.
4. **Política de Rodapé Único:**
   - Se a implementação incluir telas Livewire/Blade, nunca inclua rodapés internos. O único rodapé é o do layout `app.blade.php`.
5. **Tipagem Estrita:**
   - Todo arquivo PHP deve iniciar com `declare(strict_types=1);` e utilizar tipagem estrita de propriedades, parâmetros e retornos.

---

## 3. Fluxo Arquitetural Estrito (Passo a Passo)

A cadeia de um indicador no Monitora Fácil segue 6 camadas estritas e desacopladas:

```
[1. Migrations & Models] ──► [2. Extração DW PEC] ──► [3. Calculador de Práticas]
                                      │                         │
                                      ▼                         ▼
[6. Testes Unit & Feature] ◄── [5. Snapshot Service] ◄── [4. Busca Ativa Nominal]
```

### Passo 1: Migrations e Models de Coorte e Nominal
Criação das tabelas no banco de dados da aplicação (`database/migrations/`) e respectivos models (`app/Models/`):
- `C*CohortSnapshot`: métricas agregadas por equipe (denominador, numerador, pontuação média, distribuição por práticas).
- `C*Nominal*`: lista nominal de cidadãos da coorte com status de cada prática clínica, flags de busca ativa e pendências.
- **Ação obrigatória:** Adicionar a descrição das tabelas e colunas em `DATABASE-SCHEMA.md`.

### Passo 2: Extração DW (`app/Services/C*DwService.php`)
Serviço responsável por conectar à réplica do PostgreSQL do e-SUS PEC, extrair a coorte elegível e seus eventos clínicos:
- Uso de `declare(strict_types=1);`.
- Constante de versão (`public const VERSION = 'dw-c*-YYYY-MM-normative-v1.0';`).
- Constantes explícitas de CIDs, CIAPs, CBOs e códigos SIGTAP.
- Processamento em lotes (`CHUNK_SIZE = 100`) para evitar sobrecarga de memória.
- Consultas com `WHERE EXISTS` e filtros restritivos de tempo (`tb_dim_tempo`).

### Passo 3: Calculador de Práticas (`app/Services/C*PracticeCalculator.php`)
Serviço de lógica de negócio pura (POPO - *Plain Old PHP Object*), sem dependências de banco de dados ou queries SQL:
- Recebe arrays estruturados com datas de eventos clínicos e parâmetros da equipe/cidadão.
- Calcula cumprimento de cada prática (ex: práticas A a F), janelas de oportunidade e pontuação individual (0 a 100).
- Facilmente testável com testes unitários em milissegundos.

### Passo 4: Busca Ativa Nominal (`app/Services/C*ActiveSearchService.php`)
Serviço de inteligência clínica para a gestão e equipes de saúde:
- Classifica cidadãos entre: *Em dia*, *Alerta de prazo*, *Atrasado/Crítico*.
- Calcula ordenação por prioridade de intervenção das equipes (ACS, médico, enfermeiro).
- Disponibiliza dados para a listagem da UI, exportação CSV e fichas de busca ativa.

### Passo 5: Snapshot Service (`app/Services/C*SnapshotService.php`)
Orquestrador de persistência:
- Invoca a extração no DW PEC (`C*DwService`).
- Abre transação `DB::transaction(...)`.
- Limpa snapshots legados da mesma competência (`year`, `quarter`, `indicator_code`).
- Insere registros nominais em lotes (`array_chunk($nominalRecords, 100)`).
- Consolida e grava `C*CohortSnapshot`, `FamilyHealthMonthlySnapshot` e `FamilyHealthIndicatorSnapshot`.

### Passo 6: Testes Obrigatórios
- **Unitários (`tests/Unit/C*PracticeCalculatorTest.php`):** cobrem 100% das ramificações de regras de cálculo e janelas temporais.
- **De Feature (`tests/Feature/C*IndicatorTest.php`):** validam persistência dos snapshots, metadata do indicador, ponderação do CVAT e API/Livewire.

---

## 4. Templates Base de Código (PHP 8.2+)

### Template 1: Calculador de Práticas Puras (`app/Services/C*PracticeCalculator.php`)

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Calculador de regras e pontuações do Indicador C{X}.
 * Lógica pura, sem I/O ou banco de dados.
 */
class C{X}PracticeCalculator
{
    /**
     * @param array{
     *     reference_date: string,
     *     events: array<string, list<string>>,
     *     team_type: string
     * } $data
     * @return array{
     *     score: int,
     *     score_percent: int,
     *     practices_met: array<string, bool>,
     *     details: array<string, mixed>
     * }
     */
    public function calculate(array $data): array
    {
        $refDate = CarbonImmutable::parse($data['reference_date'])->endOfDay();
        $events = $data['events'] ?? [];
        $teamType = $data['team_type'] ?? '70';

        // Janelas regulamentares
        $windowStart = $refDate->subMonths(12)->startOfDay();

        $consults = $this->datesBetween($events['consults'] ?? [], $windowStart, $refDate);
        $exams = $this->datesBetween($events['exams'] ?? [], $windowStart, $refDate);

        $metA = count($consults) >= 1;
        $metB = count($exams) >= 1;

        $score = 0;
        if ($metA) {
            $score += 50;
        }
        if ($metB) {
            $score += 50;
        }

        return [
            'score' => $score,
            'score_percent' => min(100, $score),
            'practices_met' => [
                'A' => $metA,
                'B' => $metB,
            ],
            'details' => [
                'consult_count' => count($consults),
                'exam_count' => count($exams),
            ],
        ];
    }

    /**
     * @param list<string> $dates
     * @return list<CarbonImmutable>
     */
    private function datesBetween(array $dates, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $valid = [];
        foreach ($dates as $d) {
            if (! is_string($d) || trim($d) === '') {
                continue;
            }
            $dt = CarbonImmutable::parse($d);
            if ($dt->betweenIncluded($start, $end)) {
                $valid[] = $dt;
            }
        }
        usort($valid, static fn (CarbonImmutable $a, CarbonImmutable $b) => $a <=> $b);

        return $valid;
    }
}
```

---

### Template 2: Extração DW (`app/Services/C*DwService.php`)

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;

class C{X}DwService
{
    public const VERSION = 'dw-c{x}-2026-09-normative-v1.0';
    private const CHUNK_SIZE = 100;

    // Constantes normativas oficiais obtidas via search_aps_rules
    private const CIAPS = ['...'];
    private const CIDS = ['...'];
    private const CBOS_MEDICO = ['2251', '2252', '2253', '2231'];
    private const CBOS_ENFERMEIRO = ['2235'];

    public function __construct(
        private readonly C{X}PracticeCalculator $calculator
    ) {}

    /**
     * @param array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}> $teams
     * @return array{
     *     cohort: array<string, mixed>,
     *     nominals: list<array<string, mixed>>,
     *     scores: array<string, mixed>
     * }
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        if ($teams === []) {
            throw new RuntimeException('C{X}: Nenhuma equipe informada para extração.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $startDate = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $endDate = (clone $startDate)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today()->lt($endDate) ? Carbon::today()->endOfDay() : $endDate;

        // 1. Carrega coorte elegível com vínculo nas equipes
        $cohort = $this->loadEligibleCohort($connection, $teams, $asOf);

        // 2. Extrai eventos clínicos em lotes controlados
        $nominals = $this->processNominalRecords($connection, $cohort, $asOf);

        return [
            'cohort' => $cohort,
            'nominals' => $nominals,
            'scores' => [],
        ];
    }

    private function marks(int $count): string
    {
        return implode(',', array_fill(0, $count, '?'));
    }
}
```

---

### Template 3: Snapshot Service (`app/Services/C*SnapshotService.php`)

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\C{X}CohortSnapshot;
use App\Models\C{X}NominalRecord;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C{X}SnapshotService
{
    public function __construct(
        private readonly C{X}DwService $dw
    ) {}

    /**
     * @param array<string, array{ine:string,name:string,type:string}> $teams
     * @return array{records:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        $extraction = $this->dw->extract($pec, $year, $quarter, $teams);
        $nominals = $extraction['nominals'] ?? [];

        DB::transaction(function () use ($nominals, $teams, $year, $quarter): void {
            // Limpa dados prévios da mesma competência
            C{X}CohortSnapshot::query()->where('year', $year)->where('quarter', $quarter)->delete();
            C{X}NominalRecord::query()->where('year', $year)->where('quarter', $quarter)->delete();
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->where('indicator_code', 'c{x}')
                ->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->where('indicator_code', 'c{x}')
                ->delete();

            // Grava registros nominais em lotes
            if (! empty($nominals)) {
                foreach (array_chunk($nominals, 100) as $chunk) {
                    $records = array_map(static fn (array $d) => array_merge($d, [
                        'year' => $year,
                        'quarter' => $quarter,
                        'calculation_version' => C{X}DwService::VERSION,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]), $chunk);

                    C{X}NominalRecord::query()->insert($records);
                }
            }
        });

        return [
            'records' => count($nominals),
            'teams' => count($teams),
            'months' => 4,
        ];
    }
}
```

---

## 5. Checklist de Validação Final da Skill

Antes de considerar a criação do indicador finalizada, valide:
- [ ] O RAG normativo (`search_aps_rules`) foi consultado e os CBOs/CIDs/CIAPs estão em conformidade com as Notas do MS.
- [ ] O arquivo `DATABASE-SCHEMA.md` foi atualizado com as novas tabelas e colunas.
- [ ] O `C*DwService` utiliza tipagem estrita, lotes (`CHUNK_SIZE`) e queries auditadas sem `SELECT *`.
- [ ] O `C*PracticeCalculator` é uma classe pura e possui testes unitários cobrindo todos os cenários de janela.
- [ ] O `C*ActiveSearchService` provê ordenação correta para ações de busca ativa.
- [ ] O `C*SnapshotService` realiza persistência transacional (`DB::transaction`).
- [ ] Testes automatizados executam via `php artisan test --filter=C{X}` com 100% de sucesso.
- [ ] Não há nenhum dado simulado ou mockado.
