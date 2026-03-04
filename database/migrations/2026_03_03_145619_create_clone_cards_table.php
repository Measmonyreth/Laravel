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
        Schema::create('clone_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_number');
            $table->date('expiry_date');
            $table->string('status')->default('active');
            $table->string('type')->default('credit'); // credit or debit
            $table->decimal('amount', 10, 2);
            $table->string('cardholder_name');
            $table->string('cvv'); // cvv is sensitive, consider encrypting it in a real application
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clone_cards');
    }
};
