<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titre_poste')->nullable();
            $table->text('bio')->nullable();
            $table->string('telephone')->nullable();
            $table->string('localisation')->nullable();
            $table->json('competences')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('buildcvpro_token')->nullable();
            $table->string('buildcvpro_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};