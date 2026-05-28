<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('candidate','employer','admin','super_admin') NOT NULL DEFAULT 'candidate'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('candidate','employer','admin') NOT NULL DEFAULT 'candidate'");
    }
};
