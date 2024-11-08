// 2024_11_08_164402_create_pet_profiles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Basis informatie
            $table->string('name');                    // Naam van het huisdier
            $table->string('type');                    // Type huisdier (hond, kat, etc.)
            $table->text('description')->nullable();    // Algemene beschrijving
            // Oppas details
            $table->datetime('when_needed');           // Wanneer oppas nodig is
            $table->integer('duration');               // Duur in uren
            $table->decimal('hourly_rate', 8, 2);      // Uurtarief
            $table->text('important_info');            // Belangrijke informatie
            // Media
            $table->string('profile_photo_path')->nullable(); // Pad naar profielfoto
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pet_profiles');
    }
};