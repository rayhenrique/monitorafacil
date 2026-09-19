<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvatNominalMetric extends Model
{
    protected $fillable = [
        'year',
        'month',
        'last_record_date',
        'mici_total',
        'mici_updated',
        'mici_outdated',
        'mici_without_micdt_total',
        'mici_updated_micdt_outdated_or_none',
        'mici_updated_without_micdt',
        'mici_with_micdt_total',
        'mici_and_micdt_updated',
        'mici_and_micdt_outdated',
        'citizens_linked',
        'citizens_not_linked',
        'no_criteria_total',
        'elderly_or_child_total',
        'bpc_or_pbf_total',
        'elderly_child_and_benefit_total',
        'no_criteria_accompanied',
        'elderly_or_child_accompanied',
        'bpc_or_pbf_accompanied',
        'elderly_child_and_benefit_accompanied',
        'no_criteria_not_accompanied',
        'elderly_or_child_not_accompanied',
        'bpc_or_pbf_not_accompanied',
        'elderly_child_and_benefit_not_accompanied',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'last_record_date' => 'date',
            'mici_total' => 'integer',
            'mici_updated' => 'integer',
            'mici_outdated' => 'integer',
            'mici_without_micdt_total' => 'integer',
            'mici_updated_micdt_outdated_or_none' => 'integer',
            'mici_updated_without_micdt' => 'integer',
            'mici_with_micdt_total' => 'integer',
            'mici_and_micdt_updated' => 'integer',
            'mici_and_micdt_outdated' => 'integer',
            'citizens_linked' => 'integer',
            'citizens_not_linked' => 'integer',
            'no_criteria_total' => 'integer',
            'elderly_or_child_total' => 'integer',
            'bpc_or_pbf_total' => 'integer',
            'elderly_child_and_benefit_total' => 'integer',
            'no_criteria_accompanied' => 'integer',
            'elderly_or_child_accompanied' => 'integer',
            'bpc_or_pbf_accompanied' => 'integer',
            'elderly_child_and_benefit_accompanied' => 'integer',
            'no_criteria_not_accompanied' => 'integer',
            'elderly_or_child_not_accompanied' => 'integer',
            'bpc_or_pbf_not_accompanied' => 'integer',
            'elderly_child_and_benefit_not_accompanied' => 'integer',
        ];
    }
}
