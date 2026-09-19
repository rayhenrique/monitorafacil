<?php

namespace Tests\Unit;

use App\Services\C3PracticeCalculator;
use PHPUnit\Framework\TestCase;

class C3PracticeCalculatorTest extends TestCase
{
    public function test_it_scores_all_practices_from_documented_events_in_their_clinical_windows(): void
    {
        $result = (new C3PracticeCalculator)->calculate([
            'dum' => '2026-01-01',
            'outcome_date' => '2026-10-22',
            'puerperium_end_date' => '2026-12-03',
            'prenatal_consults' => [
                '2026-03-20', '2026-04-15', '2026-05-15', '2026-06-15',
                '2026-07-15', '2026-08-15', '2026-09-15',
            ],
            'bp_measurements' => [
                '2026-03-20', '2026-04-15', '2026-05-15', '2026-06-15',
                '2026-07-15', '2026-08-15', '2026-09-15',
            ],
            'anthropometrics' => [
                '2026-03-20', '2026-04-15', '2026-05-15', '2026-06-15',
                '2026-07-15', '2026-08-15', '2026-09-15',
            ],
            'visits' => ['2026-04-01', '2026-05-01', '2026-06-01'],
            'vaccines_dtpa' => ['2026-06-01'],
            'exams' => [
                'syphilis' => ['2026-03-01', '2026-08-20'],
                'hiv' => ['2026-03-02', '2026-08-21'],
                'hepb' => ['2026-03-03'],
                'hepc' => ['2026-03-04'],
            ],
            'puerperal_consults' => ['2026-11-05'],
            'puerperal_visits' => ['2026-11-06'],
            'oral_health' => ['2026-07-01'],
        ], '70');

        $this->assertSame(100, $result['score_percent']);
        $this->assertSame('2026-03-20', $result['first_consult_date']);
        $this->assertSame(11, $result['first_consult_gestational_week']);
        foreach (range('a', 'k') as $letter) {
            $this->assertTrue($result["practice_{$letter}_met"], "Prática {$letter} deveria estar cumprida.");
        }
    }

    public function test_it_does_not_infer_exams_or_puerperal_care_from_prenatal_consultations(): void
    {
        $result = (new C3PracticeCalculator)->calculate([
            'dum' => '2026-01-01',
            'outcome_date' => '2026-10-22',
            'puerperium_end_date' => '2026-12-03',
            'prenatal_consults' => [
                '2026-03-01', '2026-04-01', '2026-05-01', '2026-06-01',
                '2026-07-01', '2026-08-01', '2026-09-01',
            ],
            'bp_measurements' => [],
            'anthropometrics' => [],
            'visits' => ['2026-02-01', '2026-04-01', '2026-05-01'],
            'vaccines_dtpa' => ['2026-04-01'],
            'exams' => ['syphilis' => [], 'hiv' => [], 'hepb' => [], 'hepc' => []],
            'puerperal_consults' => [],
            'puerperal_visits' => [],
            'oral_health' => [],
        ], '70');

        $this->assertTrue($result['practice_a_met']);
        $this->assertTrue($result['practice_b_met']);
        $this->assertSame(2, $result['practice_e'], 'A visita anterior à primeira consulta não pode contar.');
        $this->assertFalse($result['practice_f_met'], 'dTpa anterior à 20ª semana não pode contar.');
        $this->assertFalse($result['practice_g_met']);
        $this->assertFalse($result['practice_h_met']);
        $this->assertFalse($result['practice_i_met']);
        $this->assertFalse($result['practice_j_met']);
    }

    public function test_eap_receives_the_documented_exception_for_practices_e_and_j(): void
    {
        $result = (new C3PracticeCalculator)->calculate([
            'dum' => '2026-01-01',
            'outcome_date' => '2026-10-22',
            'puerperium_end_date' => '2026-12-03',
            'prenatal_consults' => [],
            'visits' => [],
            'puerperal_visits' => [],
        ], '76');

        $this->assertTrue($result['practice_e_met']);
        $this->assertTrue($result['practice_j_met']);
        $this->assertSame(18, $result['score_percent']);
    }
}
