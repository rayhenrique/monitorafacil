<?php

namespace App\Services;

class FinancialProjectionService
{
    // Componente Vínculo e Acompanhamento Territorial, eSF 40h, Anexo XCIX-A.
    // Fonte: https://www.gov.br/saude/pt-br/assuntos/noticias/2024/junho/ministerio-da-saude-cria-faq-para-esclarecer-sobre-o-novo-financiamento-da-atencao-primaria/1o-edicao-faq-aps
    public const MONTHLY_RATES = [
        'optimal' => 8000,
        'good' => 6000,
        'sufficient' => 4000,
        'regular' => 2000,
    ];

    /** @param array{optimal: int, good: int, sufficient: int, regular: int} $distribution */
    public function monthlyAmount(array $distribution, int $totalTeams): ?int
    {
        if ($totalTeams < 0 || array_sum($distribution) !== $totalTeams) {
            return null;
        }

        $amount = 0;

        foreach (self::MONTHLY_RATES as $classification => $rate) {
            $count = $distribution[$classification] ?? null;

            if (! is_int($count) || $count < 0) {
                return null;
            }

            $amount += $count * $rate;
        }

        return $amount;
    }
}
