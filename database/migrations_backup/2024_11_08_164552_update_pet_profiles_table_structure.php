<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            // Voeg eventuele extra velden toe die je later nodig hebt
            $table->boolean('is_active')->default(true);
            $table->json('preferences')->nullable();    // Voorkeuren voor oppas
            $table->string('medical_info')->nullable(); // Medische informatie
        });
    }

    public function down()
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'preferences', 'medical_info']);
        });
    }
};