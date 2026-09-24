<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c7_nominal_women', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('social_name', 200)->nullable();
            $table->date('birth_date');
            $table->unsignedTinyInteger('age_years')->default(0);
            $table->string('sex', 20)->default('FEMININO');
            $table->string('gender_identity', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('team_type', 10)->default('70');
            $table->string('professional_cns', 20)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('microarea', 20)->nullable();
            $table->string('month_ref', 10)->nullable();

            // Vínculo e Acompanhamento Territorial
            $table->boolean('mici_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);

            // =========================================================================
            // As 4 Boas Práticas Clínicas (Quadro 01 - C7)
            // =========================================================================

            // (A) Rastreamento Câncer do Colo do Útero (25 a 64 anos) - 20 pts
            // Exame citopatológico nos últimos 36 meses ou teste molecular DNA-HPV nos últimos 60 meses
            $table->boolean('eligible_practice_a')->default(false);
            $table->boolean('practice_a_met')->default(false);
            $table->unsignedSmallInteger('practice_a_count')->default(0);
            $table->date('last_cervical_exam_date')->nullable();
            $table->string('last_cervical_exam_code', 50)->nullable();
            $table->string('last_cervical_exam_desc', 200)->nullable();

            // (B) Vacinação contra HPV (9 a 14 anos) - 30 pts
            // Ao menos 1 dose de vacina contra HPV (códigos 67 ou 93)
            $table->boolean('eligible_practice_b')->default(false);
            $table->boolean('practice_b_met')->default(false);
            $table->unsignedSmallInteger('practice_b_count')->default(0);
            $table->date('last_hpv_vaccine_date')->nullable();
            $table->string('last_hpv_vaccine_code', 20)->nullable();
            $table->string('last_hpv_vaccine_name', 100)->nullable();

            // (C) Saúde Sexual e Reprodutiva (14 a 69 anos) - 30 pts
            // Ao menos 1 consulta com CIAP/CID/Procedimento nos últimos 12 meses
            $table->boolean('eligible_practice_c')->default(false);
            $table->boolean('practice_c_met')->default(false);
            $table->unsignedSmallInteger('practice_c_count')->default(0);
            $table->date('last_sexual_health_date')->nullable();
            $table->string('last_sexual_health_code', 50)->nullable();
            $table->string('last_sexual_health_detail', 255)->nullable();

            // (D) Rastreamento Câncer de Mama (50 a 69 anos) - 20 pts
            // Exame de mamografia bilateral/rastreamento nos últimos 24 meses
            $table->boolean('eligible_practice_d')->default(false);
            $table->boolean('practice_d_met')->default(false);
            $table->unsignedSmallInteger('practice_d_count')->default(0);
            $table->date('last_mammogram_date')->nullable();
            $table->string('last_mammogram_code', 50)->nullable();
            $table->string('last_mammogram_desc', 200)->nullable();

            // Pontuação individual e pendências clínicas
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->json('pending_practices')->nullable();
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c7_nominal_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c7_nominal_women');
    }
};
