<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->decimal('principal', 12, 2);          // amount borrowed
            $table->decimal('annual_rate', 6, 3);          // nominal annual interest rate (%)
            $table->unsignedInteger('months');             // term in months
            $table->decimal('monthly_payment', 12, 2)->nullable();
            $table->decimal('total_payable', 12, 2)->nullable();
            $table->decimal('total_interest', 12, 2)->nullable();
            $table->decimal('effective_rate', 6, 3)->nullable(); // effective annual rate (%)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
