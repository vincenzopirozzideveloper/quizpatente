<?php
// database/migrations/2025_06_08_000002_create_ministerial_quiz_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministerial_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministerial_quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministerial_quiz_questions');
    }
};