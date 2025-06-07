<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theory_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subtopic_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('code')->nullable(); // es: "1.1.1"

            $table->longText('content');
            $table->json('media')->nullable();

            $table->string('image_url')->nullable();
            $table->string('image_caption')->nullable();
            $table->enum('image_position', ['before', 'after'])->default('before');

            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['topic_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theory_contents');
    }
};
