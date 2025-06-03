<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('error_count')->default(1);
            $table->timestamp('last_error_date');
            $table->timestamp('last_correct_date')->nullable();
            $table->boolean('is_mastered')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'is_mastered']);
            $table->index('error_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_errors');
    }
};