<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eerst checken of de kolom bestaat en zo ja, verwijderen
        if (Schema::hasColumn('sitting_requests', 'status')) {
            Schema::table('sitting_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        // Dan de nieuwe ENUM kolom toevoegen
        Schema::table('sitting_requests', function (Blueprint $table) {
            $table->enum('status', ['open', 'accepted', 'rejected', 'completed'])
                  ->default('open')
                  ->after('extra_informatie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sitting_requests', function (Blueprint $table) {
            $table->dropColumn('status');
            // Zet het terug naar de originele situatie als je wilt terugdraaien
            $table->string('status')->default('open');
        });
    }
};