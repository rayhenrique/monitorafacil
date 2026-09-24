<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_cohort_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 150);
            $table->string('team_type', 10);
            $table->unsignedInteger('cohort_total');
            $table->unsignedInteger('evaluated_total');
            $table->json('monthly_counts');
            $table->date('as_of');
            $table->string('calculation_version', 40);
            $table->timestamps();

            $table->unique(['year', 'quarter', 'ine'], 'unique_c4_cohort_team_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_cohort_snapshots');
    }
};
