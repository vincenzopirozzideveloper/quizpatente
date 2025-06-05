<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theory_contents', function (Blueprint $table) {
            $table->foreignId('subtopic_id')->nullable()->after('topic_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable()->after('id'); // es: "1.1.1"
            $table->dropColumn('title'); // Lo rimuoviamo perché useremo il code
        });
    }

    public function down(): void
    {
        Schema::table('theory_contents', function (Blueprint $table) {
            $table->dropForeign(['subtopic_id']);
            $table->dropColumn('subtopic_id');
            $table->dropColumn('code');
            $table->string('title')->after('topic_id');
        });
    }
};