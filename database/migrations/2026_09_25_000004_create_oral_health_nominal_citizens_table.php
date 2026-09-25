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
        Schema::create('oral_health_nominal_citizens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedBigInteger('cidadao_pec_id')->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('mother_name', 200)->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedSmallInteger('age_years')->default(0);
            $table->char('gender', 1)->nullable();
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('microarea', 20)->nullable()->index();
            $table->string('district', 100)->nullable();
            $table->string('professional_cns', 30)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->boolean('mici_updated')->default(false)->index();
            $table->boolean('micdt_updated')->default(false)->index();
            $table->boolean('is_linked')->default(false)->index();

            // Indicadores B1 a B6
            $table->unsignedSmallInteger('b1_count')->default(0)->index();
            $table->unsignedSmallInteger('b2_count')->default(0)->index();
            $table->unsignedSmallInteger('b3_count')->default(0)->index();
            $table->unsignedSmallInteger('b4_count')->default(0)->index();
            $table->boolean('b4_eligible')->default(false)->index(); // true se idade entre 6 e 12 anos
            $table->unsignedSmallInteger('b5_count')->default(0)->index();
            $table->unsignedSmallInteger('b6_count')->default(0)->index();

            // Atributos clínicos odontológicos
            $table->date('first_consultation_date')->nullable();
            $table->date('treatment_completed_date')->nullable();
            $table->date('last_brushing_date')->nullable();
            $table->date('last_attendance_date')->nullable();
            $table->string('last_professional_name', 200)->nullable();
            $table->string('last_professional_cbo', 20)->nullable();
            $table->string('treatment_status', 30)->default('nao_iniciado'); // 'concluido', 'em_andamento', 'nao_iniciado'

            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_oral_nom_cit_period_ine');
            $table->index(['year', 'quarter', 'cnes'], 'idx_oral_nom_cit_period_cnes');
            $table->index(['year', 'quarter', 'cidadao_pec_id'], 'idx_oral_nom_cit_period_pec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oral_health_nominal_citizens');
    }
};
