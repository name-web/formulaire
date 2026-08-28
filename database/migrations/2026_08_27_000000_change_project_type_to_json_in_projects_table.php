<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('project_type')->nullable()->change();
        });

        // Migrate existing single values to JSON arrays
        $projects = DB::table('projects')->whereNotNull('project_type')->get();
        foreach ($projects as $project) {
            $current = $project->project_type;
            if ($current && ! str_starts_with($current, '[')) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['project_type' => json_encode([$current])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_type')->nullable()->change();
        });
    }
};
