<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence')->index();
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quote_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('text');
            $table->string('title');
            $table->json('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quote_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 20)->default('text');
            $table->text('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('footer')->nullable();
            $table->string('legal_info')->nullable();
            $table->string('currency', 10)->default('FCFA');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_custom_fields');
        Schema::dropIfExists('quote_sections');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('company_settings');
    }
};
