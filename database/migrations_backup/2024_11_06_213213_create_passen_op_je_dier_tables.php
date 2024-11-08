<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check en voeg admin kolommen toe aan users
        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false);
                $table->boolean('is_blocked')->default(false);
            });
        }

        // Oppasprofielen
        if (!Schema::hasTable('sitter_profiles')) {
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
        }

        // Huisdierprofielen
        if (!Schema::hasTable('pet_profiles')) {
            Schema::create('pet_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('naam');
                $table->string('soort');
                $table->text('beschrijving');
                $table->json('belangrijke_info');
                $table->string('profielfoto_pad')->nullable();
                $table->timestamps();
            });
        }

        // Oppasvragen
        if (!Schema::hasTable('sitting_requests')) {
            Schema::create('sitting_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pet_profile_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('sitter_profile_id')->constrained()->onDelete('cascade');
                $table->datetime('start_datum');
                $table->datetime('eind_datum');
                $table->decimal('uurtarief', 8, 2);
                $table->text('extra_informatie')->nullable();
                $table->enum('status', ['open', 'accepted', 'rejected', 'completed'])->default('open');
                $table->timestamps();
            });
        }

        // Reacties op oppasvragen
        if (!Schema::hasTable('sitting_responses')) {
            Schema::create('sitting_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sitting_request_id')->constrained()->onDelete('cascade');
                $table->foreignId('sitter_profile_id')->constrained()->onDelete('cascade');
                $table->text('bericht');
                $table->enum('status', ['in_afwachting', 'geaccepteerd', 'geweigerd'])->default('in_afwachting');
                $table->timestamps();
            });
        }

        // Reviews
        if (!Schema::hasTable('reviews')) {
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
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('sitting_responses');
        Schema::dropIfExists('sitting_requests');
        Schema::dropIfExists('pet_profiles');
        Schema::dropIfExists('sitter_profiles');
        
        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['is_admin', 'is_blocked']);
            });
        }
    }
};