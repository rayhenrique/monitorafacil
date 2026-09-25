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
        Schema::create('oral_health_nominal_patients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('indicator_code', 10)->index(); // b1, b2, b4, b5, b6
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('social_name', 200)->nullable();
            $table->date('birth_date');
            $table->unsignedTinyInteger('age_years')->default(0);
            $table->string('phone', 30)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('professional_cbo', 20)->nullable();

            // Atributos clínicos odontológicos
            $table->date('first_consultation_date')->nullable();
            $table->date('treatment_completed_date')->nullable();
            $table->string('treatment_status', 30)->default('em_andamento'); // 'concluido', 'em_andamento', 'nao_iniciado', 'atrasado'
            $table->boolean('has_first_consultation')->default(false);
            $table->boolean('has_treatment_completed')->default(false);
            $table->boolean('has_supervised_brushing')->default(false);
            $table->date('last_brushing_date')->nullable();
            $table->unsignedSmallInteger('preventive_procedures_count')->default(0);
            $table->unsignedSmallInteger('restorative_procedures_count')->default(0);
            $table->unsignedSmallInteger('art_procedures_count')->default(0);
            $table->unsignedSmallInteger('exodontia_procedures_count')->default(0);
            $table->unsignedSmallInteger('total_procedures_count')->default(0);

            // Pontuação individual do cidadão / registro
            $table->decimal('score_percent', 8, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'indicator_code', 'ine'], 'idx_oral_nominal_period_ine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oral_health_nominal_patients');
    }
};
