<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Étape 1 — Infos de base
            $table->string('nom_entreprise');
            $table->string('type_entreprise')->nullable();
            $table->string('secteur')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();

            // Étape 2 — Localisation & Contact
            $table->string('pays')->nullable();
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();
            $table->string('email_contact')->nullable();
            $table->string('telephone')->nullable();
            $table->string('site_web')->nullable();

            // Étape 3 — Responsable du compte
            $table->string('responsable_nom')->nullable();
            $table->string('responsable_fonction')->nullable();
            $table->string('responsable_telephone')->nullable();
            $table->string('responsable_email')->nullable();

            // Étape 4 — Infos légales
            $table->string('numero_identification')->nullable();
            $table->string('annee_creation')->nullable();
            $table->string('nombre_employes')->nullable();

            // Statut du compte
            $table->enum('statut', ['pending', 'active', 'rejected'])->default('pending');
            $table->text('raison_rejet')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};