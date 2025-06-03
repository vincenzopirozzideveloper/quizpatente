<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->boolean('correct_answer');
            $table->text('explanation')->nullable();
            $table->string('image_url')->nullable();
            $table->json('media')->nullable();
            $table->integer('difficulty_level')->default(1);
            $table->boolean('is_ministerial')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['topic_id', 'is_active']);
            $table->index('is_ministerial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};