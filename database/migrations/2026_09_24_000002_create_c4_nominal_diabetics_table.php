<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_nominal_diabetics', function (Blueprint $table): void {
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
            $table->string('phone', 30)->nullable();
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('professional_cns', 20)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('microarea', 20)->nullable();

            // Dados da Condição de Diabetes
            $table->string('ciap_codes', 100)->nullable();
            $table->string('cid_codes', 100)->nullable();
            $table->date('first_diagnosis_date')->nullable();
            $table->date('last_diagnosis_date')->nullable();
            $table->string('condition_status', 30)->default('ativo'); // 'ativo', 'latente'
            $table->string('month_ref', 10)->nullable();

            // Vínculo e Acompanhamento Territorial
            $table->boolean('mici_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);

            // Contagens e Metas das 6 Boas Práticas Clínicas (Quadro 01)
            // (A) Consulta Médica/Enfermagem nos últimos 6 meses (20 pts)
            $table->unsignedSmallInteger('practice_a')->default(0);
            $table->boolean('practice_a_met')->default(false);
            $table->date('last_consultation_date')->nullable();

            // (B) Aferição de PA nos últimos 6 meses (15 pts)
            $table->unsignedSmallInteger('practice_b')->default(0);
            $table->boolean('practice_b_met')->default(false);
            $table->date('last_pa_date')->nullable();
            $table->string('last_pa_value', 30)->nullable();

            // (C) Antropometria Simultânea nos últimos 12 meses (15 pts)
            $table->unsignedSmallInteger('practice_c')->default(0);
            $table->boolean('practice_c_met')->default(false);
            $table->date('last_anthropometry_date')->nullable();
            $table->decimal('last_weight', 5, 2)->nullable();
            $table->decimal('last_height', 5, 2)->nullable();

            // (D) Visitas ACS com intervalo >= 30 dias nos últimos 12 meses (20 pts)
            $table->unsignedSmallInteger('practice_d')->default(0);
            $table->boolean('practice_d_met')->default(false);
            $table->date('last_visit_date')->nullable();

            // (E) Hemoglobina Glicada nos últimos 12 meses (15 pts)
            $table->unsignedSmallInteger('practice_e')->default(0);
            $table->boolean('practice_e_met')->default(false);
            $table->date('last_hba1c_date')->nullable();
            $table->string('last_hba1c_type', 50)->nullable();

            // (F) Avaliação dos Pés nos últimos 12 meses (15 pts)
            $table->unsignedSmallInteger('practice_f')->default(0);
            $table->boolean('practice_f_met')->default(false);
            $table->date('last_foot_exam_date')->nullable();

            // Pontuação individual do cidadão (0.00 a 100.00)
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c4_nominal_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_nominal_diabetics');
    }
};
