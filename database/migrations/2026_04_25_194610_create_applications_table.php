<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_offer_id')->constrained('job_offers')->onDelete('cascade');

            // Statut de la candidature
            $table->enum('status', ['pending', 'interview', 'accepted', 'rejected'])->default('pending');

            // CV utilisé
            $table->string('cv_path')->nullable();           // CV uploadé manuellement
            $table->string('buildcvpro_cv_id')->nullable();  // ID du CV BuildCVPro utilisé
            $table->string('message')->nullable();           // Message de motivation

            $table->timestamps();
        });

        // Table offres sauvegardées
        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_offer_id')->constrained('job_offers')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['candidate_id', 'job_offer_id']); // Pas de doublon
        });

        // Table messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('saved_jobs');
        Schema::dropIfExists('applications');
    }
};