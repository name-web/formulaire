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
        Schema::create('question_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->foreignId('depends_on_question_id')->constrained('questions')->restrictOnDelete();
            $table->string('operator');
            $table->foreignId('option_value_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->string('text_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_conditions');
    }
};
