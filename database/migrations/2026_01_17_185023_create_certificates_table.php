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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('pk_certificate_id');
            $table->unsignedBigInteger('paket_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('template_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreign('paket_id')->references('pk_paket_id')->on('pakets')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('pk_transaction_id')->on('transactions')->cascadeOnDelete();
            $table->foreign('template_id')->references('pk_certificate_template_id')->on('certificate_templates')->cascadeOnDelete();

            $table->string('certificate_number')->unique();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
