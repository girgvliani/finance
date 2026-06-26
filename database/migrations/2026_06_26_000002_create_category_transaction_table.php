<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table for the many-to-many relationship
        // between Transactions and Categories.
        Schema::create('category_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['transaction_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_transaction');
    }
};
