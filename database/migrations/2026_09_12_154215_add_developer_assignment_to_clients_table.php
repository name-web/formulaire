<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('assigned_developer_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('next_action')->nullable()->after('assigned_developer_id');
            $table->date('next_action_date')->nullable()->after('next_action');

            $table->index('next_action_date');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['next_action_date']);
            $table->dropConstrainedForeignId('assigned_developer_id');
            $table->dropColumn(['next_action', 'next_action_date']);
        });
    }
};
