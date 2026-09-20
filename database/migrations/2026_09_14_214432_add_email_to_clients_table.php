<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Adresse e-mail du client
    |--------------------------------------------------------------------------
    |
    | Le nouveau formulaire demande une adresse e-mail valide (c'est là que
    | part le devis). La colonne avait été retirée ; on la réintroduit en
    | nullable et sans index unique : les clients historiques restent à null
    | (aucun remplissage rétroactif) et deux prospects peuvent partager une
    | adresse sans bloquer une soumission.
    |
    */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
