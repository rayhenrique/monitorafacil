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
        Schema::create('family_health_monthly_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1 a 12
            $table->unsignedTinyInteger('quarter'); // 1 a 3
            $table->unsignedTinyInteger('month_in_quarter'); // 1 a 4 (ex: Mês 1, Mês 2, Mês 3, Mês 4)
            $table->string('ine', 20)->nullable()->index(); // null = consolidado municipal
            $table->string('team_name', 150)->nullable();
            $table->string('team_type', 10)->default('70'); // 70 = eSF, 76 = eAP
            $table->string('indicator_code', 10)->index(); // 'c1'
            $table->unsignedInteger('numerator')->default(0); // Demanda programada
            $table->unsignedInteger('denominator')->default(0); // Demanda total (programada + espontânea)
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular'); // otimo, bom, suficiente, regular
            $table->timestamps();

            $table->unique(['year', 'month', 'ine', 'indicator_code'], 'unique_monthly_team_indicator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_health_monthly_snapshots');
    }
};
