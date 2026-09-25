<?php

declare(strict_types=1);

namespace App\Models\OralHealth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OralHealthMonthlySnapshot extends Model
{
    use HasFactory;

    protected $table = 'oral_health_monthly_snapshots';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'month',
        'quarter',
        'month_in_quarter',
        'ine',
        'team_name',
        'cnes',
        'facility_name',
        'team_type',
        'indicator_code',
        'numerator',
        'denominator',
        'score_percent',
        'performance_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'quarter' => 'integer',
            'month_in_quarter' => 'integer',
            'numerator' => 'integer',
            'denominator' => 'integer',
            'score_percent' => 'decimal:2',
        ];
    }
}
