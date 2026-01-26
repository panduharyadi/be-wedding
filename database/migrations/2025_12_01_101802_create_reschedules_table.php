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
        Schema::create('reschedules', function (Blueprint $table) {
            $table->id('pk_reschedule_id');
            $table->unsignedBigInteger('paket_id');
            $table->unsignedBigInteger('transaction_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('paket_id')->references('pk_paket_id')->on('pakets')->onDelete('cascade');
            $table->foreign('transaction_id')->references('pk_transaction_id')->on('transactions')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('jam');
            $table->text('alasan')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reschedules');
    }
};
