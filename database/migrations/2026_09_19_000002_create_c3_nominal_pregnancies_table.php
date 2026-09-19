<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c3_nominal_pregnancies', function (Blueprint $table): void {
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

            // Dados da Gestação / Puerpério
            $table->date('dum')->nullable();
            $table->date('dpp')->nullable();
            $table->date('outcome_date')->nullable();
            $table->date('puerperium_end_date')->nullable();
            $table->unsignedTinyInteger('gestational_age_weeks')->default(0);
            $table->string('current_status', 30)->default('gestante'); // 'gestante', 'puerpera', 'encerrada'
            $table->string('month_ref', 10)->nullable();

            // Vínculo e Acompanhamento Territorial
            $table->boolean('mici_updated')->default(false);
            $table->boolean('micdt_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);

            // Contagens das 11 Boas Práticas Clínicas
            $table->unsignedSmallInteger('practice_a')->default(0);
            $table->unsignedSmallInteger('practice_b')->default(0);
            $table->unsignedSmallInteger('practice_c')->default(0);
            $table->unsignedSmallInteger('practice_d')->default(0);
            $table->unsignedSmallInteger('practice_e')->default(0);
            $table->unsignedSmallInteger('practice_f')->default(0);
            $table->unsignedSmallInteger('practice_g')->default(0);
            $table->unsignedSmallInteger('practice_h')->default(0);
            $table->unsignedSmallInteger('practice_i')->default(0);
            $table->unsignedSmallInteger('practice_j')->default(0);
            $table->unsignedSmallInteger('practice_k')->default(0);

            // Metas das 11 Boas Práticas Clínicas (Met = true/false)
            $table->boolean('practice_a_met')->default(false);
            $table->boolean('practice_b_met')->default(false);
            $table->boolean('practice_c_met')->default(false);
            $table->boolean('practice_d_met')->default(false);
            $table->boolean('practice_e_met')->default(false);
            $table->boolean('practice_f_met')->default(false);
            $table->boolean('practice_g_met')->default(false);
            $table->boolean('practice_h_met')->default(false);
            $table->boolean('practice_i_met')->default(false);
            $table->boolean('practice_j_met')->default(false);
            $table->boolean('practice_k_met')->default(false);

            // Pontuação individual do cidadão (0.00 a 100.00)
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c3_nominal_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c3_nominal_pregnancies');
    }
};
