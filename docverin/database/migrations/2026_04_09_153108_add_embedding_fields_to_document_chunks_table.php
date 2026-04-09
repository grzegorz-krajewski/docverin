<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->string('embedding_model')->nullable()->after('character_count');
            $table->longText('embedding_json')->nullable()->after('embedding_model');
        });
    }

    public function down(): void
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropColumn([
                'embedding_model',
                'embedding_json',
            ]);
        });
    }
};