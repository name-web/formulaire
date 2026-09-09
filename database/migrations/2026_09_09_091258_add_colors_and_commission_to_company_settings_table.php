<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->default('#F5A623')->after('currency');
            $table->string('secondary_color', 7)->default('#1a1a1a')->after('primary_color');
            $table->unsignedInteger('commission_threshold')->default(100000)->after('secondary_color');
            $table->unsignedInteger('commission_percent')->default(20)->after('commission_threshold');
            $table->unsignedInteger('commission_flat')->default(20000)->after('commission_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color', 'secondary_color',
                'commission_threshold', 'commission_percent', 'commission_flat',
            ]);
        });
    }
};
