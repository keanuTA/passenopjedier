<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Sitter Profiles tabel
        Schema::create('sitter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('ervaring');              
            $table->text('over_mij');              
            $table->json('huisdier_voorkeuren');    
            $table->decimal('uurtarief', 8, 2);    
            $table->boolean('is_beschikbaar')->default(true);
            $table->string('profielfoto_pad')->nullable();
            $table->string('video_intro_pad')->nullable();
            $table->timestamps();
        });

        // 2. Pet Profiles tabel (aangepast aan je formulier velden)
        Schema::create('pet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');                    // Naam van het huisdier
            $table->string('type');                    // Type huisdier
            $table->datetime('when_needed');           // Wanneer oppas nodig
            $table->integer('duration');               // Duur in uren
            $table->decimal('hourly_rate', 8, 2);      // Uurtarief
            $table->json('important_info');            // Nu als JSON ipv text
            $table->timestamps();
        });

        // 3. Sitting Requests tabel
        Schema::create('sitting_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sitter_profile_id')->constrained()->onDelete('cascade');
            $table->datetime('start_datum');
            $table->datetime('eind_datum');
            $table->decimal('uurtarief', 8, 2);
            $table->text('extra_informatie')->nullable();
            $table->enum('status', ['open', 'toegewezen', 'rejected', 'completed'])->default('open');
            $table->timestamps();
        });

        // 4. Reviews tabel
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sitting_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sitter_profile_id')->constrained()->onDelete('cascade');
            $table->integer('rating');
            $table->text('review_text');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('sitting_requests');
        Schema::dropIfExists('pet_profiles');
        Schema::dropIfExists('sitter_profiles');
    }
};