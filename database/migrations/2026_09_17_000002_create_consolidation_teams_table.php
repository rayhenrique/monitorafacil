<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consolidation_teams', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->enum('type', ['esf', 'esaude_bucal', 'emulti']);
            $table->unsignedInteger('total_active');
            $table->timestamps();

            $table->unique(['year', 'quarter', 'type']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE consolidation_teams ADD CONSTRAINT consolidation_teams_quarter_check CHECK (quarter BETWEEN 1 AND 3)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidation_teams');
    }
};
