<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use App\Models\OralHealth\OralHealthNominalPatient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class OralHealthActiveSearchService
{
    /**
     * @param array{
     *     indicator_code?: string|null,
     *     year?: int|null,
     *     quarter?: int|null,
     *     search?: string|null,
     *     ine?: string|null,
     *     cnes?: string|null,
     *     status?: string|null,
     *     per_page?: int|null
     * } $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = $this->buildQuery($filters);

        $perPage = (int) ($filters['per_page'] ?? 30);

        return $query->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getMetrics(array $filters): array
    {
        $base = $this->buildQuery($filters, false);

        $total = (clone $base)->count();
        $completed = (clone $base)->where('treatment_status', 'concluido')->count();
        $inProgress = (clone $base)->where('treatment_status', 'em_andamento')->count();
        $hasFirstConsult = (clone $base)->where('has_first_consultation', true)->count();
        $hasBrushing = (clone $base)->where('has_supervised_brushing', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'has_first_consult' => $hasFirstConsult,
            'has_brushing' => $hasBrushing,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return Builder<OralHealthNominalPatient>
     */
    private function buildQuery(array $filters, bool $withOrdering = true): Builder
    {
        $query = OralHealthNominalPatient::query();

        if (! empty($filters['indicator_code'])) {
            $query->where('indicator_code', strtolower((string) $filters['indicator_code']));
        }

        if (! empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        if (! empty($filters['quarter'])) {
            $query->where('quarter', (int) $filters['quarter']);
        }

        if (! empty($filters['ine'])) {
            $query->where('ine', (string) $filters['ine']);
        }

        if (! empty($filters['cnes'])) {
            $query->where('cnes', (string) $filters['cnes']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('treatment_status', (string) $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $cleanDigits = preg_replace('/\D+/', '', $term);

            $query->where(function (Builder $q) use ($term, $cleanDigits): void {
                $q->where('name', 'like', "%{$term}%");

                if ($cleanDigits !== '' && strlen($cleanDigits) >= 3) {
                    $q->orWhere('cpf', 'like', "%{$cleanDigits}%")
                      ->orWhere('cns', 'like', "%{$cleanDigits}%");
                }
            });
        }

        if ($withOrdering) {
            $query->orderBy('name', 'asc');
        }

        return $query;
    }
}
