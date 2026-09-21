<?php

namespace App\Services;

use InvalidArgumentException;

/** Deterministic rules from NT 30/2025 and NT 8/2026; never substitutes missing inputs. */
class CvatNtCalculator
{
    public function parameter(int $municipalPopulation, string $teamType, ?int $weeklyHours = null): int
    {
        if ($municipalPopulation <= 0) {
            throw new InvalidArgumentException('População IBGE ausente.');
        }
        $range = match (true) {
            $municipalPopulation <= 20000 => [2000, 1500, 1000],
            $municipalPopulation <= 50000 => [2500, 1875, 1250],
            $municipalPopulation <= 100000 => [2750, 2063, 1375],
            default => [3000, 2250, 1500],
        };
        $parameter = match (true) {
            $teamType === '70' => $range[0],
            $teamType === '76' && $weeklyHours === 30 => $range[1],
            $teamType === '76' && $weeklyHours === 20 => $range[2],
            default => throw new InvalidArgumentException('Tipo de equipe ou carga horária eAP não aferível.'),
        };

        return min($municipalPopulation, $parameter);
    }

    /** @return array{index: float, score: float} */
    public function registration(int $miciOnly, int $miciAndMicdt, int $parameter): array
    {
        if ($parameter <= 0 || $miciOnly < 0 || $miciAndMicdt < 0) {
            throw new InvalidArgumentException('Entradas de cadastro inválidas.');
        }
        $raw = (($miciOnly * 0.75) + ($miciAndMicdt * 1.5)) / $parameter * 100;

        return ['index' => round($raw, 2), 'score' => $this->score($raw, 3.0)];
    }

    /** @return array{index: float, score: float} */
    public function monitoring(int $none, int $age, int $benefit, int $both, int $parameter): array
    {
        if ($parameter <= 0 || min($none, $age, $benefit, $both) < 0) {
            throw new InvalidArgumentException('Entradas de acompanhamento inválidas.');
        }
        $raw = (($none * 1.0) + ($age * 1.2) + ($benefit * 1.3) + ($both * 2.5)) / $parameter * 100;

        return ['index' => round($raw, 2), 'score' => $this->score($raw, 7.0)];
    }

    public function satisfactionBonus(int $assessed, int $totalAttendances): float
    {
        if ($assessed < 0 || $totalAttendances < 0 || $assessed > $totalAttendances) {
            throw new InvalidArgumentException('Totais de satisfação inválidos.');
        }
        if ($assessed === 0 || $totalAttendances === 0) {
            return 0.0;
        }

        return ($assessed / $totalAttendances) >= 0.05 ? 0.30 : 0.15;
    }

    /**
     * @param  list<array{registration: float, monitoring: float, bonus: float}>  $months
     * @return array{registration: float, monitoring: float, bonus: float, final: float, classification: string}
     */
    public function quarter(array $months, bool $exceededRegistrationCeiling = false): array
    {
        if (count($months) !== 4) {
            throw new InvalidArgumentException('A NT 8/2026 exige quatro competências mensais para fechar o quadrimestre.');
        }
        $registration = array_sum(array_column($months, 'registration')) / 4;
        $monitoring = array_sum(array_column($months, 'monitoring')) / 4;
        $bonus = max(array_column($months, 'bonus'));
        $final = round($registration + min(7.0, $monitoring + $bonus), 2);
        $classification = match (true) {
            $final > 8.5 => 'ÓTIMO',
            $final >= 7.0 => 'BOM',
            $final >= 5.0 => 'SUFICIENTE',
            default => 'REGULAR',
        };
        if ($exceededRegistrationCeiling && $classification === 'ÓTIMO') {
            $classification = 'BOM';
        }

        return compact('registration', 'monitoring', 'bonus', 'final', 'classification');
    }

    private function score(float $index, float $max): float
    {
        $factor = $max / 4;

        return match (true) {
            $index > 85 => $factor * 4,
            $index >= 65 => $factor * 3,
            $index >= 45 => $factor * 2,
            default => $factor,
        };
    }
}
