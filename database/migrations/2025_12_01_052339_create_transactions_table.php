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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('pk_transaction_id');
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('paket_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('paket_id')->references('pk_paket_id')->on('pakets')->onDelete('cascade');
            $table->string('nama_paket');
            $table->integer('total');
            $table->dateTime('tanggal');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
