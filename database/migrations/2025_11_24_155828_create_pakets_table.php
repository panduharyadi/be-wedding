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
        Schema::create('pakets', function (Blueprint $table) {
            $table->id('pk_paket_id');
            $table->string('image')->nullable();
            $table->string('name');
            $table->integer('price');
            $table->string('durasi')->nullable();
            $table->json('benefits');
            $table->boolean('is_rias')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakets');
    }
};
