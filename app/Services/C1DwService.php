<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use InvalidArgumentException;

class C1DwService
{
    public const VERSION = 'dw-c1-nt08-2026-v1';

    /** @var list<string> */
    private const ELIGIBLE_CBOS = [
        '225142',
        '225170',
        '225130',
        '225125',
        '225250',
        '223565',
        '223505',
    ];

    /**
     * Extrai os atendimentos que compõem o C1, agrupados por INE e competência.
     *
     * @param  list<int>  $months
     * @param  list<string>  $eligibleInes
     * @return array<string, array<int, array{numerator: int, denominator: int}>>
     */
    public function extract(
        ConnectionInterface $connection,
        int $year,
        array $months,
        array $eligibleInes
    ): array {
        $months = array_values(array_unique(array_map('intval', $months)));
        $eligibleInes = array_values(array_unique(array_filter(array_map(
            static fn (mixed $ine): string => trim((string) $ine),
            $eligibleInes
        ))));

        if ($months === [] || $eligibleInes === []) {
            return [];
        }

        if (array_filter($months, static fn (int $month): bool => $month < 1 || $month > 12) !== []) {
            throw new InvalidArgumentException('A lista de competências do C1 contém um mês inválido.');
        }

        $monthPlaceholders = implode(', ', array_fill(0, count($months), '?'));
        $inePlaceholders = implode(', ', array_fill(0, count($eligibleInes), '?'));
        $cboPlaceholders = implode(', ', array_fill(0, count(self::ELIGIBLE_CBOS), '?'));

        $sql = <<<SQL
            SELECT
                t.nu_mes,
                e.nu_ine,
                COUNT(*) FILTER (
                    WHERE ta.nu_identificador::text IN ('1', '2')
                ) AS num_programada,
                COUNT(*) AS den_total
            FROM tb_fat_atendimento_individual fai
            INNER JOIN tb_dim_tempo t
                ON fai.co_dim_tempo = t.co_seq_dim_tempo
            INNER JOIN tb_dim_equipe e
                ON fai.co_dim_equipe_1 = e.co_seq_dim_equipe
            INNER JOIN tb_dim_cbo c
                ON fai.co_dim_cbo_1 = c.co_seq_dim_cbo
            INNER JOIN tb_dim_profissional p
                ON fai.co_dim_profissional_1 = p.co_seq_dim_profissional
            INNER JOIN tb_dim_tipo_atendimento ta
                ON fai.co_dim_tipo_atendimento = ta.co_seq_dim_tipo_atendimento
            WHERE t.nu_ano = ?
              AND t.nu_mes IN ({$monthPlaceholders})
              AND e.nu_ine::text IN ({$inePlaceholders})
              AND REPLACE(COALESCE(c.nu_cbo::text, ''), '-', '') IN ({$cboPlaceholders})
              AND ta.nu_identificador::text IN ('1', '2', '4', '5', '6')
              AND REGEXP_REPLACE(COALESCE(p.nu_cns::text, ''), '[^0-9]', '', 'g') ~ '^[0-9]{15}$'
              AND fai.dt_nascimento IS NOT NULL
              AND (
                    REGEXP_REPLACE(COALESCE(fai.nu_cpf_cidadao::text, ''), '[^0-9]', '', 'g') ~ '^[0-9]{11}$'
                 OR REGEXP_REPLACE(COALESCE(fai.nu_cns::text, ''), '[^0-9]', '', 'g') ~ '^[0-9]{15}$'
              )
            GROUP BY t.nu_mes, e.nu_ine
            ORDER BY e.nu_ine, t.nu_mes
            SQL;

        $bindings = array_merge([$year], $months, $eligibleInes, self::ELIGIBLE_CBOS);
        $results = [];

        foreach ($connection->select($sql, $bindings) as $row) {
            $ine = trim((string) $row->nu_ine);
            $month = (int) $row->nu_mes;

            if (! in_array($ine, $eligibleInes, true) || ! in_array($month, $months, true)) {
                continue;
            }

            $results[$ine][$month] = [
                'numerator' => (int) $row->num_programada,
                'denominator' => (int) $row->den_total,
            ];
        }

        return $results;
    }
}
