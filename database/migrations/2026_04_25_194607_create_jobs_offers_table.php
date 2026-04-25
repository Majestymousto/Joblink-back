<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->onDelete('cascade');

            // Infos principales
            $table->string('titre');
            $table->string('excerpt')->nullable();         // Résumé court affiché dans la liste
            $table->longText('description');               // HTML complet du poste
            $table->json('requirements')->nullable();      // Profil recherché (liste)
            $table->json('perks')->nullable();             // Ce qu'on offre (icon, label, desc)

            // Détails du poste
            $table->string('type_contrat');                // cdi, cdd, stage, freelance, alternance
            $table->string('secteur')->nullable();
            $table->string('localisation')->nullable();
            $table->string('salaire')->nullable();
            $table->string('experience')->nullable();      // Ex: "2 – 4 ans"
            $table->string('niveau_etude')->nullable();
            $table->json('competences')->nullable();       // Skills requis

            // Dates et statut
            $table->date('date_expiration')->nullable();
            $table->enum('statut', ['active', 'fermée', 'expirée'])->default('active');

            // Stats
            $table->integer('vues')->default(0);
            $table->integer('candidatures_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};