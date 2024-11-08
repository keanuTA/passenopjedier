<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sitter_profiles', function (Blueprint $table) {
            $table->json('huisdier_voorkeuren')->nullable();
            $table->text('ervaring')->nullable();
            $table->text('over_mij')->nullable();
            $table->json('beschikbare_tijden')->nullable();
            $table->decimal('uurtarief', 8, 2)->nullable();
            $table->string('profielfoto_pad')->nullable();
            $table->string('video_intro_pad')->nullable();
            $table->boolean('is_beschikbaar')->default(true);
            $table->json('service_gebied')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sitter_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'huisdier_voorkeuren',
                'ervaring',
                'over_mij',
                'beschikbare_tijden',
                'uurtarief',
                'profielfoto_pad',
                'video_intro_pad',
                'is_beschikbaar',
                'service_gebied'
            ]);
        });
    }
};