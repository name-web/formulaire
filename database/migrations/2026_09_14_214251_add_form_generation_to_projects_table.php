<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Génération de formulaire du projet
    |--------------------------------------------------------------------------
    |
    | Migration strictement additive : les projets déjà en production restent
    | sur la génération « legacy » (défaut de la colonne) et conservent leurs
    | réponses historiques dans les tables questions / answers, inchangées.
    |
    | Les nouveaux projets portent la génération du nouveau formulaire et
    | stockent leurs réponses structurées dans `form_data`.
    |
    */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('form_version', 30)->default('legacy')->after('status')->index();
            $table->json('form_data')->nullable()->after('form_version');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['form_version']);
            $table->dropColumn(['form_version', 'form_data']);
        });
    }
};
