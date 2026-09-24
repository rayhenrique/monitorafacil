<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c6_cohort_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('team_type', 10)->nullable();
            $table->unsignedInteger('cohort_total')->default(0);
            $table->unsignedInteger('evaluated_total')->default(0);
            $table->json('monthly_counts')->nullable();
            $table->date('as_of')->nullable();
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c6_cohort_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c6_cohort_snapshots');
    }
};
