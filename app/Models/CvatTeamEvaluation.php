<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvatTeamEvaluation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'year',
        'quarter',
        'quarter_label',
        'cnes',
        'facility_name',
        'ine',
        'team_type',
        'team_name',
        'registration_score',
        'monitoring_score',
        'final_score',
        'final_classification',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'registration_score' => 'float',
            'monitoring_score' => 'float',
            'final_score' => 'float',
        ];
    }
}
