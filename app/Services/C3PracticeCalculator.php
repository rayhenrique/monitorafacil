<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Calcula as 11 práticas do C3 exclusivamente dentro das janelas clínicas.
 */
class C3PracticeCalculator
{
    /**
     * @param  array<string, mixed>  $pregnancy
     * @return array<string, int|bool|null>
     */
    public function calculate(array $pregnancy, string $teamType): array
    {
        $dum = CarbonImmutable::parse($pregnancy['dum'])->startOfDay();
        $outcome = CarbonImmutable::parse($pregnancy['outcome_date'])->startOfDay();
        $puerperiumEnd = CarbonImmutable::parse($pregnancy['puerperium_end_date'])->endOfDay();

        $prenatal = $this->datesBetween($pregnancy['prenatal_consults'] ?? [], $dum, $outcome);
        $firstConsult = $prenatal[0] ?? null;
        $firstGestationalWeek = $firstConsult ? intdiv($dum->diffInDays($firstConsult), 7) : null;

        $bp = $this->datesBetween($pregnancy['bp_measurements'] ?? [], $dum, $outcome);
        $anthropometrics = $this->datesBetween($pregnancy['anthropometrics'] ?? [], $dum, $outcome);
        $visits = $firstConsult
            ? $this->datesBetween($pregnancy['visits'] ?? [], $firstConsult->addDay()->startOfDay(), $outcome)
            : [];
        $vaccines = $this->datesBetween($pregnancy['vaccines_dtpa'] ?? [], $dum->addWeeks(20), $outcome);
        $oralHealth = $this->datesBetween($pregnancy['oral_health'] ?? [], $dum, $outcome);
        $puerperalConsults = $this->datesBetween($pregnancy['puerperal_consults'] ?? [], $outcome->addDay(), $puerperiumEnd);
        $puerperalVisits = $this->datesBetween($pregnancy['puerperal_visits'] ?? [], $outcome->addDay(), $puerperiumEnd);

        $trimester1End = $dum->addWeeks(14)->subDay()->endOfDay();
        $trimester3Start = $dum->addWeeks(28)->startOfDay();
        $exams = $pregnancy['exams'] ?? [];
        $hasSyphilis1 = $this->hasDateBetween($exams['syphilis'] ?? [], $dum, $trimester1End);
        $hasHiv1 = $this->hasDateBetween($exams['hiv'] ?? [], $dum, $trimester1End);
        $hasHepB1 = $this->hasDateBetween($exams['hepb'] ?? [], $dum, $trimester1End);
        $hasHepC1 = $this->hasDateBetween($exams['hepc'] ?? [], $dum, $trimester1End);
        $hasSyphilis3 = $this->hasDateBetween($exams['syphilis'] ?? [], $trimester3Start, $outcome);
        $hasHiv3 = $this->hasDateBetween($exams['hiv'] ?? [], $trimester3Start, $outcome);

        $met = [
            'a' => $firstGestationalWeek !== null && $firstGestationalWeek <= 12,
            'b' => count($prenatal) >= 7,
            'c' => count($bp) >= 7,
            'd' => count($anthropometrics) >= 7,
            'e' => $teamType === '76' || count($visits) >= 3,
            'f' => count($vaccines) >= 1,
            'g' => $hasSyphilis1 && $hasHiv1 && $hasHepB1 && $hasHepC1,
            'h' => $hasSyphilis3 && $hasHiv3,
            'i' => count($puerperalConsults) >= 1,
            'j' => $teamType === '76' || count($puerperalVisits) >= 1,
            'k' => count($oralHealth) >= 1,
        ];

        $score = ($met['a'] ? 10 : 0);
        foreach (range('b', 'k') as $letter) {
            $score += $met[$letter] ? 9 : 0;
        }

        return [
            'first_consult_date' => $firstConsult?->toDateString(),
            'first_consult_gestational_week' => $firstGestationalWeek,
            'practice_a' => $met['a'] ? 1 : 0,
            'practice_b' => count($prenatal),
            'practice_c' => count($bp),
            'practice_d' => count($anthropometrics),
            'practice_e' => count($visits),
            'practice_f' => count($vaccines),
            'practice_g' => $met['g'] ? 1 : 0,
            'practice_h' => $met['h'] ? 1 : 0,
            'practice_i' => count($puerperalConsults),
            'practice_j' => count($puerperalVisits),
            'practice_k' => count($oralHealth),
            'has_syphilis_1st_tri' => $hasSyphilis1,
            'has_hiv_1st_tri' => $hasHiv1,
            'has_hepb_1st_tri' => $hasHepB1,
            'has_hepc_1st_tri' => $hasHepC1,
            'has_syphilis_3rd_tri' => $hasSyphilis3,
            'has_hiv_3rd_tri' => $hasHiv3,
            ...array_combine(
                array_map(static fn (string $letter): string => "practice_{$letter}_met", range('a', 'k')),
                array_values($met)
            ),
            'score_percent' => $score,
        ];
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return array<int, CarbonImmutable>
     */
    private function datesBetween(array $values, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $dates = [];
        foreach ($values as $key => $value) {
            $raw = is_array($value) ? ($value['date'] ?? $key) : (is_bool($value) ? $key : $value);
            if (! is_string($raw) || trim($raw) === '') {
                continue;
            }

            $date = CarbonImmutable::parse($raw);
            if ($date->betweenIncluded($start, $end)) {
                $dates[$date->toDateString()] = $date;
            }
        }

        ksort($dates);

        return array_values($dates);
    }

    /** @param array<int|string, mixed> $values */
    private function hasDateBetween(array $values, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return $this->datesBetween($values, $start, $end) !== [];
    }
}
