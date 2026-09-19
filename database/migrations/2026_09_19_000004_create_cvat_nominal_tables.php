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
        Schema::create('cvat_nominal_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->default(2026);
            $table->unsignedTinyInteger('month')->default(12);
            $table->date('last_record_date')->nullable();

            // Dimensão Cadastro
            $table->unsignedInteger('mici_total')->default(0);
            $table->unsignedInteger('mici_updated')->default(0);
            $table->unsignedInteger('mici_outdated')->default(0);
            $table->unsignedInteger('mici_without_micdt_total')->default(0);
            $table->unsignedInteger('mici_updated_micdt_outdated_or_none')->default(0);
            $table->unsignedInteger('mici_updated_without_micdt')->default(0);
            $table->unsignedInteger('mici_with_micdt_total')->default(0);
            $table->unsignedInteger('mici_and_micdt_updated')->default(0);
            $table->unsignedInteger('mici_and_micdt_outdated')->default(0);
            $table->unsignedInteger('citizens_linked')->default(0);
            $table->unsignedInteger('citizens_not_linked')->default(0);

            // Dimensão Acompanhamento
            $table->unsignedInteger('no_criteria_total')->default(0);
            $table->unsignedInteger('elderly_or_child_total')->default(0);
            $table->unsignedInteger('bpc_or_pbf_total')->default(0);
            $table->unsignedInteger('elderly_child_and_benefit_total')->default(0);

            $table->unsignedInteger('no_criteria_accompanied')->default(0);
            $table->unsignedInteger('elderly_or_child_accompanied')->default(0);
            $table->unsignedInteger('bpc_or_pbf_accompanied')->default(0);
            $table->unsignedInteger('elderly_child_and_benefit_accompanied')->default(0);

            $table->unsignedInteger('no_criteria_not_accompanied')->default(0);
            $table->unsignedInteger('elderly_or_child_not_accompanied')->default(0);
            $table->unsignedInteger('bpc_or_pbf_not_accompanied')->default(0);
            $table->unsignedInteger('elderly_child_and_benefit_not_accompanied')->default(0);

            $table->timestamps();

            $table->unique(['year', 'month']);
        });

        Schema::create('cvat_nominal_citizens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cidadao_pec_id')->unique();
            $table->string('cns', 20)->nullable();
            $table->string('cpf', 20)->nullable();
            $table->string('responsible_cns_cpf', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('name', 190);
            $table->unsignedSmallInteger('age')->default(0);
            $table->string('race_color', 40)->default('Não informada');
            $table->string('gender', 10)->nullable();
            $table->string('cnes', 16)->nullable();
            $table->string('facility_name', 190)->nullable();
            $table->string('ine', 16)->nullable();
            $table->string('team_name', 190)->nullable();
            $table->string('professional_cns', 30)->nullable();
            $table->string('professional_name', 190)->nullable();
            $table->string('microarea', 10)->default('00');

            // Dimensão Cadastro
            $table->boolean('mici_updated')->default(true);
            $table->date('mici_date')->nullable();
            $table->boolean('micdt_updated')->default(true);
            $table->date('micdt_date')->nullable();
            $table->boolean('has_micdt')->default(true);
            $table->boolean('is_linked')->default(true);

            // Dimensão Acompanhamento
            $table->string('vulnerability_type', 30)->default('sem_criterio'); // idoso, crianca, sem_criterio
            $table->string('social_benefit', 30)->default('nenhum'); // bpc, pbf, bpc_pbf, nenhum
            $table->boolean('is_accompanied')->default(true);
            $table->date('last_visit_date')->nullable();
            $table->string('address', 255)->nullable();

            $table->unsignedSmallInteger('year')->default(2026);
            $table->unsignedTinyInteger('month')->default(12);
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('cns');
            $table->index('cpf');
            $table->index('name');
            $table->index('ine');
            $table->index('cnes');
            $table->index('microarea');
            $table->index('professional_cns');
            $table->index('vulnerability_type');
            $table->index('social_benefit');
            $table->index('is_accompanied');
            $table->index('mici_updated');
            $table->index('micdt_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvat_nominal_citizens');
        Schema::dropIfExists('cvat_nominal_metrics');
    }
};
