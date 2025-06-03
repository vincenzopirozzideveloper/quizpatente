<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('daily_reminder')->default(false);
            $table->time('reminder_time')->nullable();
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->enum('difficulty_preference', ['easy', 'medium', 'hard', 'mixed'])->default('mixed');
            $table->boolean('show_explanations')->default(true);
            $table->boolean('show_timer')->default(true);
            $table->boolean('sound_effects')->default(true);
            $table->integer('questions_per_session')->default(40);
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};