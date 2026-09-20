<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Devis provisoire généré par le nouveau formulaire
    |--------------------------------------------------------------------------
    |
    | Colonnes additives et nullables : les devis existants restent inchangés
    | (calculation = null, project_id = null) et ne sont jamais recalculés.
    |
    | Pour un devis issu du nouveau formulaire, `calculation` conserve le
    | calcul complet défini dans le dossier fonctionnel : lignes de création,
    | frais récurrents, niveaux, TVA, remise, majoration, échéancier et délai.
    | `project_id` rattache ce devis au projet qui l'a généré.
    |
    */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->json('calculation')->nullable()->after('prestations');
            $table->foreignId('project_id')->nullable()->after('client_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn('calculation');
        });
    }
};
