<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consolidation_registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedInteger('mici_updated_count');
            $table->unsignedInteger('mici_outdated_count');
            $table->unsignedInteger('micdt_updated_count');
            $table->unsignedInteger('micdt_outdated_count');
            $table->timestamps();

            $table->unique(['year', 'quarter']);
        });

        DB::statement(
            'ALTER TABLE consolidation_registrations ADD CONSTRAINT consolidation_registrations_quarter_check CHECK (quarter BETWEEN 1 AND 3)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidation_registrations');
    }
};
