<?php

declare(strict_types=1);

namespace App\Models\OralHealth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OralHealthIndicatorSnapshot extends Model
{
    use HasFactory;

    protected $table = 'oral_health_indicator_snapshots';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'quarter',
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
        'good_practices_breakdown',
        'active_search_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'numerator' => 'integer',
            'denominator' => 'integer',
            'score_percent' => 'decimal:2',
            'good_practices_breakdown' => 'array',
            'active_search_count' => 'integer',
        ];
    }
}
