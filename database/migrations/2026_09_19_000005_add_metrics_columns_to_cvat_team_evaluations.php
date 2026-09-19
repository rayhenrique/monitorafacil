<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cvat_team_evaluations', function (Blueprint $table) {
            $table->unsignedSmallInteger('parameter')->default(2500)->after('team_type');
            $table->unsignedInteger('linked_registrations')->default(0)->after('parameter');
            $table->decimal('linked_ratio', 6, 2)->default(0.00)->after('linked_registrations');
            $table->decimal('registration_result', 6, 2)->default(0.00)->after('linked_ratio');
            $table->decimal('monitoring_result', 6, 2)->default(0.00)->after('registration_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cvat_team_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'parameter',
                'linked_registrations',
                'linked_ratio',
                'registration_result',
                'monitoring_result',
            ]);
        });
    }
};
