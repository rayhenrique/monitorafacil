<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c7_cohort_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('team_type', 10)->nullable();
            $table->unsignedInteger('cohort_total')->default(0);
            $table->unsignedInteger('evaluated_total')->default(0);

            // Detalhamento das 4 Boas Práticas Clínicas (Quadro 01 - C7)
            $table->unsignedInteger('practice_a_eligible')->default(0);
            $table->unsignedInteger('practice_a_compliant')->default(0);
            $table->decimal('practice_a_score', 5, 2)->default(0.00);

            $table->unsignedInteger('practice_b_eligible')->default(0);
            $table->unsignedInteger('practice_b_compliant')->default(0);
            $table->decimal('practice_b_score', 5, 2)->default(0.00);

            $table->unsignedInteger('practice_c_eligible')->default(0);
            $table->unsignedInteger('practice_c_compliant')->default(0);
            $table->decimal('practice_c_score', 5, 2)->default(0.00);

            $table->unsignedInteger('practice_d_eligible')->default(0);
            $table->unsignedInteger('practice_d_compliant')->default(0);
            $table->decimal('practice_d_score', 5, 2)->default(0.00);

            $table->decimal('final_score', 5, 2)->default(0.00);

            $table->json('monthly_counts')->nullable();
            $table->date('as_of')->nullable();
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c7_cohort_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c7_cohort_snapshots');
    }
};
