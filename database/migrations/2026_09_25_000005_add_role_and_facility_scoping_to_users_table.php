<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('operator')->after('remember_token');
            $table->string('cnes', 20)->nullable()->index()->after('role');
            $table->string('facility_name', 255)->nullable()->after('cnes');
        });

        // Os usuários existentes no sistema são promovidos a administradores municipais
        DB::table('users')->update([
            'role' => 'admin',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['cnes']);
            $table->dropColumn(['role', 'cnes', 'facility_name']);
        });
    }
};
