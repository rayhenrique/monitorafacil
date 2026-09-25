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
        Schema::create('oral_health_indicator_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index(); // null = consolidado municipal
            $table->string('team_name', 150)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('team_type', 10)->default('88'); // 87 = eSB Mod I, 88 = eSB Mod II
            $table->string('indicator_code', 10)->index(); // b1, b2, b3, b4, b5, b6
            $table->unsignedInteger('numerator')->default(0);
            $table->unsignedInteger('denominator')->default(0);
            $table->decimal('score_percent', 8, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular'); // otimo, bom, suficiente, regular
            $table->json('good_practices_breakdown')->nullable();
            $table->unsignedInteger('active_search_count')->default(0);
            $table->timestamps();

            $table->unique(['year', 'quarter', 'ine', 'indicator_code'], 'unique_oral_team_indicator_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oral_health_indicator_snapshots');
    }
};
