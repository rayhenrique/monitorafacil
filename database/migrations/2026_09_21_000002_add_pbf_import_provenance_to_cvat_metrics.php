<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cvat_nominal_metrics', function (Blueprint $table): void {
            $table->unsignedBigInteger('pbf_import_id')->nullable();
            $table->string('pbf_vigencia', 6)->nullable();
            $table->unsignedInteger('pbf_confirmed_total')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cvat_nominal_metrics', function (Blueprint $table): void {
            $table->dropColumn(['pbf_import_id', 'pbf_vigencia', 'pbf_confirmed_total']);
        });
    }
};
