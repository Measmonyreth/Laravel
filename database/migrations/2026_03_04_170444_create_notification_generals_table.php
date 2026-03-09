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
        Schema::create('notification_generals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('large_image')->nullable(); // ✅ Add image column
            $table->string('big_image')->nullable(); // ✅ Add image column
            $table->string('route');
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_generals');
    }
};
