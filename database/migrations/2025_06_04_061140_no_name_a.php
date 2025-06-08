<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('theory_content_id')
                ->nullable()
                ->after('topic_id')
                ->constrained('theory_contents')
                ->nullOnDelete();

            $table->string('ministerial_number', 20)
                ->nullable()
                ->after('order')
                ->comment('Numero domanda ministeriale es: D001');

            $table->index('theory_content_id');
            $table->index('ministerial_number');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['theory_content_id']);

            $table->dropIndex(['theory_content_id']);
            $table->dropIndex(['ministerial_number']);

            $table->dropColumn([
                'theory_content_id',
                'ministerial_number'
            ]);
        });
    }
};