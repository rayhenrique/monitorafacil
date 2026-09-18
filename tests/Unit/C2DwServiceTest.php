<?php

namespace Tests\Unit;

use App\Services\C2DwService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\TestCase;

class C2DwServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    private function child(): array
    {
        $born = Carbon::create(2024, 1, 1)->startOfDay();
        $consultations = [];
        $measurements = [];
        for ($i = 1; $i <= 9; $i++) {
            $date = (clone $born)->addDays($i * 20)->toDateString();
            $consultations[(string) $i] = ['date' => $date, 'remote' => false];
            $measurements[$date] = ['weight' => true, 'height' => true];
        }

        $vaccines = [];
        foreach ([60, 120, 180] as $day) {
            $vaccines[] = ['date' => (clone $born)->addDays($day)->toDateString(), 'code' => 43];
        }
        foreach ([60, 120] as $day) {
            $vaccines[] = ['date' => (clone $born)->addDays($day)->toDateString(), 'code' => 26];
        }
        foreach ([366, 430] as $day) {
            $vaccines[] = ['date' => (clone $born)->addDays($day)->toDateString(), 'code' => 24];
        }

        return [
            'born' => $born,
            'birthday' => (clone $born)->addYearsNoOverflow(2),
            'consultations' => $consultations,
            'measurements' => $measurements,
            'visits' => ['2024-01-20' => true, '2024-04-10' => true],
            'vaccines' => $vaccines,
        ];
    }

    public function test_five_practices_are_scored_from_real_events(): void
    {
        $this->assertSame(['A' => true, 'B' => true, 'C' => true, 'D' => true, 'E' => true],
            (new C2DwService)->scoreChild($this->child(), '70'));
    }

    public function test_eap_gets_visit_points_and_remote_consultation_does_not_count_for_first_visit(): void
    {
        $child = $this->child();
        $child['visits'] = [];
        $child['consultations']['1']['remote'] = true;

        $service = new C2DwService;
        $this->assertFalse($service->scoreChild($child, '70')['D']);
        $this->assertTrue($service->scoreChild($child, '76')['D']);
        $this->assertFalse($service->scoreChild($child, '76')['A']);
        $this->assertTrue($service->scoreChild($child, '76')['B']);
    }

    public function test_duplicate_measurement_days_and_early_scr_do_not_complete_practices(): void
    {
        $child = $this->child();
        $child['measurements'] = ['2024-01-20' => ['weight' => true, 'height' => true]];
        $child['vaccines'][6]['date'] = '2024-11-01';

        $score = (new C2DwService)->scoreChild($child, '70');
        $this->assertFalse($score['C']);
        $this->assertFalse($score['E']);
    }

    public function test_extraction_groups_birthday_cohort_without_fake_fallback(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->times(6)->andReturn(
            [(object) ['id' => 7, 'born' => '2024-01-15', 'ine' => '0000171220']],
            [], [], [], [], []
        );
        $result = (new C2DwService)->extract($connection, 2026, 1, [
            '0000171220' => ['ine' => '0000171220', 'name' => 'eAP', 'type' => '76'],
        ]);

        $this->assertSame(1, $result['scores']['0000171220'][1]['denominator']);
        $this->assertSame(20, $result['scores']['0000171220'][1]['numerator']);
        $this->assertSame(20.0, $result['scores']['0000171220'][1]['score_percent']);
        $this->assertSame(['A' => 0, 'B' => 0, 'C' => 0, 'D' => 1, 'E' => 0],
            $result['scores']['0000171220'][1]['practices']);
        $this->assertSame([1 => 1], $result['cohort']['0000171220']);
    }

    public function test_current_quadrimester_scores_all_birthdays_and_tracks_completed_separately(): void
    {
        Carbon::setTestNow('2026-09-18');
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->times(6)->andReturn(
            [
                (object) ['id' => 7, 'born' => '2024-09-10', 'ine' => '0000171220'],
                (object) ['id' => 8, 'born' => '2024-12-01', 'ine' => '0000171220'],
            ],
            [], [], [], [], []
        );

        $result = (new C2DwService)->extract($connection, 2026, 3, [
            '0000171220' => ['ine' => '0000171220', 'name' => 'ESF', 'type' => '70'],
        ]);

        $this->assertSame([9 => 1, 12 => 1], $result['cohort']['0000171220']);
        $this->assertSame([9 => 1], $result['completed']['0000171220']);
        $this->assertSame(1, $result['scores']['0000171220'][9]['denominator']);
        $this->assertSame(1, $result['scores']['0000171220'][12]['denominator']);
        $this->assertSame(0.0, $result['scores']['0000171220'][12]['score_percent']);
        $this->assertSame('2026-09-18', $result['as_of']);
    }

    public function test_future_only_cohort_receives_preview_without_completed_birthday(): void
    {
        Carbon::setTestNow('2026-09-18');
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->times(6)->andReturn(
            [(object) ['id' => 8, 'born' => '2024-12-01', 'ine' => '0000171220']],
            [], [], [], [], []
        );

        $result = (new C2DwService)->extract($connection, 2026, 3, [
            '0000171220' => ['ine' => '0000171220', 'name' => 'ESF', 'type' => '70'],
        ]);

        $this->assertSame(0.0, $result['scores']['0000171220'][12]['score_percent']);
        $this->assertSame(1, $result['scores']['0000171220'][12]['denominator']);
        $this->assertSame([12 => 1], $result['cohort']['0000171220']);
        $this->assertSame([], $result['completed']);
    }

    public function test_preview_does_not_read_events_dated_after_extraction(): void
    {
        Carbon::setTestNow('2026-09-18');
        $calls = 0;
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->times(6)->andReturnUsing(function ($sql, $bindings) use (&$calls) {
            $calls++;
            if ($calls === 1) {
                return [(object) ['id' => 8, 'born' => '2024-12-01', 'ine' => '0000171220']];
            }
            $this->assertStringContainsString('t.dt_registro <= ?', $sql);
            $this->assertContains('2026-09-18', $bindings);
            $this->assertNotContains('2026-12-31', $bindings);

            return [];
        });

        (new C2DwService)->extract($connection, 2026, 3, [
            '0000171220' => ['ine' => '0000171220', 'name' => 'ESF', 'type' => '70'],
        ]);
    }

    public function test_preview_scores_children_in_every_month_of_the_quadrimester(): void
    {
        Carbon::setTestNow('2026-09-18');
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')->times(6)->andReturn(
            [
                (object) ['id' => 1, 'born' => '2024-09-01', 'ine' => '0000171220'],
                (object) ['id' => 2, 'born' => '2024-10-01', 'ine' => '0000171220'],
                (object) ['id' => 3, 'born' => '2024-11-01', 'ine' => '0000171220'],
                (object) ['id' => 4, 'born' => '2024-12-01', 'ine' => '0000171220'],
            ],
            [], [], [], [], []
        );

        $result = (new C2DwService)->extract($connection, 2026, 3, [
            '0000171220' => ['ine' => '0000171220', 'name' => 'eAP', 'type' => '76'],
        ]);

        $this->assertSame([9 => 1, 10 => 1, 11 => 1, 12 => 1], $result['cohort']['0000171220']);
        $this->assertSame([9 => 1], $result['completed']['0000171220']);
        foreach ([9, 10, 11, 12] as $month) {
            $this->assertSame(1, $result['scores']['0000171220'][$month]['denominator']);
            $this->assertSame(20.0, $result['scores']['0000171220'][$month]['score_percent']);
        }
    }
}
