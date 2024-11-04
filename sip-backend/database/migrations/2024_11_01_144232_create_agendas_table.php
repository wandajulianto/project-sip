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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul agenda
            $table->text('description')->nullable(); // Deskripsi agenda
            $table->date('date'); // Tanggal pelayanan
            $table->time('time')->nullable(); // Waktu pelayanan
            $table->string('location')->nullable(); // Lokasi pelayanan
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // ID kader yang membuat agenda
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
