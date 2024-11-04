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
        Schema::create('pertumbuhans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balita_id')->constrained('balitaS')->onDelete('cascade'); // ID balita (mengacu pada tabel `children`)
            $table->date('date'); // Tanggal pengukuran
            $table->decimal('weight', 5, 2); // Berat badan (kg)
            $table->decimal('height', 5, 2); // Tinggi badan (cm)
            $table->decimal('head_circumference', 5, 2)->nullable(); // Lingkar kepala (opsional)
            $table->text('notes')->nullable(); // Catatan tambahan (opsional)
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade'); // ID kader yang mencatat data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
