<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sitting_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pet_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('sitter_profile_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_datum');
            $table->dateTime('eind_datum');
            $table->decimal('uurtarief', 8, 2);
            $table->text('extra_informatie');
            $table->enum('status', ['open', 'accepted', 'rejected'])->default('open');
        });
    }

    public function down(): void
    {
        Schema::table('sitting_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['pet_profile_id']);
            $table->dropForeign(['sitter_profile_id']);
            $table->dropColumn([
                'user_id',
                'pet_profile_id',
                'sitter_profile_id',
                'start_datum',
                'eind_datum',
                'uurtarief',
                'extra_informatie',
                'status'
            ]);
        });
    }
};