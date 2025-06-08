<?php
// database/migrations/2025_06_08_000001_create_ministerial_quizzes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministerial_quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('max_errors')->default(3); // Massimo 3 errori
            $table->timestamps();
            
            $table->index('order');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministerial_quizzes');
    }
};