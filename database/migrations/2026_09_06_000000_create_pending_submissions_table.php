<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Données temporaires de la soumission
            $table->json('client_data');
            $table->json('project_data');
            $table->json('answers_data');
            $table->json('files_data')->nullable();

            // Statut technique
            $table->string('status')->default('collecting');

            // Décision métier (séparée du statut technique)
            $table->string('decision')->nullable();

            // Suivi des erreurs
            $table->text('failure_reason')->nullable();
            $table->text('last_error')->nullable();

            // Timestamps de suivi
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Index pour les requêtes de suivi
            $table->index('status');
            $table->index('decision');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_submissions');
    }
};
