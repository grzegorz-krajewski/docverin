<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qa_query_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_chunk_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 10, 6)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_sources');
    }
};