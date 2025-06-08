<?php
// database/migrations/[timestamp]_update_quiz_sessions_type_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Per MySQL, dobbiamo fare un workaround per modificare l'enum
        DB::statement("ALTER TABLE quiz_sessions MODIFY COLUMN type ENUM('ministerial', 'ministerial_manual', 'topic', 'errors_review', 'custom') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quiz_sessions MODIFY COLUMN type ENUM('ministerial', 'topic', 'errors_review', 'custom') NOT NULL");
    }
};