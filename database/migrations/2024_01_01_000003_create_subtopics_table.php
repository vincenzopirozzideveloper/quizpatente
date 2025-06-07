<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // es: "1.1", "1.2"
            $table->string('title'); // es: "Strada", "Carreggiata"
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['topic_id', 'code']);
            $table->index(['topic_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtopics');
    }
};