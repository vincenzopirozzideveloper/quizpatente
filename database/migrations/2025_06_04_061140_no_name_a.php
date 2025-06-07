<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Aggiunge il collegamento diretto al contenuto teorico
            $table->foreignId('theory_content_id')
                ->nullable()
                ->after('topic_id')
                ->constrained('theory_contents')
                ->nullOnDelete();
                
            // Aggiunge il collegamento al subtopic per facilità di query
            $table->foreignId('subtopic_id')
                ->nullable()
                ->after('theory_content_id')
                ->constrained('subtopics')
                ->nullOnDelete();
                
            // Aggiunge un campo per il numero della domanda ministeriale (se applicabile)
            $table->string('ministerial_number', 20)
                ->nullable()
                ->after('order')
                ->comment('Numero domanda ministeriale es: D001');
                
            // Indici per ottimizzare le query
            $table->index(['topic_id', 'subtopic_id']);
            $table->index('theory_content_id');
            $table->index('ministerial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['theory_content_id']);
            $table->dropForeign(['subtopic_id']);
            
            $table->dropIndex(['topic_id', 'subtopic_id']);
            $table->dropIndex(['theory_content_id']);
            $table->dropIndex(['ministerial_number']);
            
            $table->dropColumn([
                'theory_content_id',
                'subtopic_id',
                'ministerial_number'
            ]);
        });
    }
};