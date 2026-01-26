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
        Schema::create('paket_schedules', function (Blueprint $table) {
            $table->id('pk_paket_schedule_id');
            $table->unsignedBigInteger('paket_id');
            $table->foreign('paket_id')->references('pk_paket_id')->on('pakets')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_schedules');
    }
};
