<?php
// database/migrations/2025_06_08_000003_add_ministerial_quiz_id_to_quiz_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->foreignId('ministerial_quiz_id')
                ->nullable()
                ->after('topic_id')
                ->constrained('ministerial_quizzes')
                ->nullOnDelete();
                
            // Aggiorna il tipo di quiz per distinguere i ministeriali predefiniti
            $table->dropColumn('type');
        });
        
        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->enum('type', [
                'ministerial',          // Quiz ministeriali predefiniti
                'topic',               // Quiz per argomento (automatico)
                'errors_review',       // Ripasso errori (automatico)
                'custom'              // Altri tipi personalizzati
            ])->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            $table->dropForeign(['ministerial_quiz_id']);
            $table->dropColumn('ministerial_quiz_id');
        });
    }
};