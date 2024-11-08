<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            // Check of kolommen bestaan voor we ze aanpassen
            if (Schema::hasColumn('pet_profiles', 'description')) {
                $table->dropColumn('description');
            }
            
            if (!Schema::hasColumn('pet_profiles', 'name')) {
                $table->string('name')->after('user_id');
            }
            
            if (!Schema::hasColumn('pet_profiles', 'type')) {
                $table->string('type')->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            $table->dropColumn(['name', 'type']);
            $table->string('description')->after('user_id');
        });
    }
};