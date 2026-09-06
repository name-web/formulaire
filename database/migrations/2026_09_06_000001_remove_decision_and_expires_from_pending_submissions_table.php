<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_submissions', function (Blueprint $table) {
            $table->dropColumn(['decision', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('pending_submissions', function (Blueprint $table) {
            $table->string('decision')->nullable()->after('status');
            $table->timestamp('expires_at')->nullable()->after('imported_at');
        });
    }
};
