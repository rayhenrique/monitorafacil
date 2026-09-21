<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cvat_nominal_citizens', function (Blueprint $table): void {
            $table->string('source', 32)->nullable()->index();
            $table->unsignedSmallInteger('care_contacts')->default(0);
            $table->unsignedSmallInteger('total_contacts')->default(0);
            $table->boolean('registration_eligible')->default(false);
        });

        Schema::table('cvat_nominal_metrics', function (Blueprint $table): void {
            $table->string('source', 32)->nullable()->index();
            $table->date('reference_date')->nullable();
            $table->boolean('benefit_data_available')->default(false);
            $table->unsignedInteger('excluded_without_pec_id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('cvat_nominal_metrics', function (Blueprint $table): void {
            $table->dropColumn(['source', 'reference_date', 'benefit_data_available', 'excluded_without_pec_id']);
        });
        Schema::table('cvat_nominal_citizens', function (Blueprint $table): void {
            $table->dropColumn(['source', 'care_contacts', 'total_contacts', 'registration_eligible']);
        });
    }
};
