<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_topic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->integer('completed_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            $table->decimal('accuracy_rate', 5, 2)->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('completed_question_ids')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'topic_id']);
            $table->index('accuracy_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_topic_progress');
    }
};