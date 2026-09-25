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
        Schema::create('oral_health_monthly_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1 a 12
            $table->unsignedTinyInteger('quarter'); // 1 a 3
            $table->unsignedTinyInteger('month_in_quarter'); // 1 a 4
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
            $table->timestamps();

            $table->unique(['year', 'month', 'ine', 'indicator_code'], 'unique_oral_monthly_team_indicator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oral_health_monthly_snapshots');
    }
};
