<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            // Eerst verwijderen we de 'description' kolom
            $table->dropColumn('description');
            
            // Dan voegen we de nieuwe kolommen toe
            $table->string('name')->after('user_id');
            $table->string('type')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            // In de rollback doen we het omgekeerde
            $table->dropColumn(['name', 'type']);
            $table->string('description')->after('user_id');
        });
    }
};