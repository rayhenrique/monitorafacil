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
        Schema::create('cvat_dimension_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('quarter_label', 16);
            $table->string('team_type', 16)->default('eSF');
            $table->string('dimension_code', 32); // 'cadastro', 'acompanhamento'
            $table->string('dimension_name', 128);
            $table->unsignedSmallInteger('regular_count')->default(0);
            $table->unsignedSmallInteger('sufficient_count')->default(0);
            $table->unsignedSmallInteger('good_count')->default(0);
            $table->unsignedSmallInteger('optimal_count')->default(0);
            $table->unsignedSmallInteger('total_teams')->default(0);
            $table->timestamps();

            $table->unique(['year', 'quarter', 'team_type', 'dimension_code'], 'cvat_dist_year_quarter_type_dim_unique');
            $table->index(['year', 'quarter']);
        });

        Schema::create('cvat_team_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('quarter_label', 16);
            $table->string('cnes', 16);
            $table->string('facility_name', 190);
            $table->string('ine', 16);
            $table->string('team_type', 16)->default('eSF');
            $table->string('team_name', 190);
            $table->decimal('registration_score', 5, 2)->default(0.00); // 0.00 a 3.00
            $table->decimal('monitoring_score', 5, 2)->default(0.00);   // 0.00 a 7.00
            $table->decimal('final_score', 5, 2)->default(0.00);        // 0.00 a 10.00
            $table->string('final_classification', 32);                 // ÓTIMO, BOM, SUFICIENTE, REGULAR
            $table->timestamps();

            $table->unique(['year', 'quarter', 'ine'], 'cvat_team_year_quarter_ine_unique');
            $table->index(['year', 'quarter']);
            $table->index('ine');
            $table->index('final_classification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvat_team_evaluations');
        Schema::dropIfExists('cvat_dimension_distributions');
    }
};
