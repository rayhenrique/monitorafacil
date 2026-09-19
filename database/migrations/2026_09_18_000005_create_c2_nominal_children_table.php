<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c2_nominal_children', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('mother_name', 200)->nullable();
            $table->date('birth_date');
            $table->unsignedSmallInteger('age_months')->default(0);
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('professional_cns', 20)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('month_ref', 10)->nullable();
            $table->string('microarea', 20)->nullable();
            $table->boolean('mici_updated')->default(false);
            $table->boolean('micdt_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);
            $table->unsignedSmallInteger('practice_a')->default(0);
            $table->unsignedSmallInteger('practice_b')->default(0);
            $table->unsignedSmallInteger('practice_c')->default(0);
            $table->unsignedSmallInteger('practice_d')->default(0);
            $table->unsignedSmallInteger('practice_e')->default(0);
            $table->boolean('practice_a_met')->default(false);
            $table->boolean('practice_b_met')->default(false);
            $table->boolean('practice_c_met')->default(false);
            $table->boolean('practice_d_met')->default(false);
            $table->boolean('practice_e_met')->default(false);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();

            $table->index(['year', 'quarter', 'ine'], 'idx_c2_nominal_period_ine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c2_nominal_children');
    }
};
