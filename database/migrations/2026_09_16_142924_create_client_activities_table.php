<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            /*
             * The author of the action. Kept nullable and null-on-delete so the
             * journal survives the removal of a commercial account.
             */
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('type', 40);
            $table->string('description');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('client_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_activities');
    }
};
