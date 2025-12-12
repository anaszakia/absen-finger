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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama potongan: Terlambat, Alpha, BPJS, dll
            $table->string('type'); // 'per_day', 'per_hour', 'fixed', 'percentage'
            $table->decimal('amount', 15, 2)->default(0); // Jumlah potongan
            $table->decimal('percentage', 5, 2)->default(0); // Persentase dari gaji
            $table->boolean('auto_calculate')->default(false); // Otomatis hitung dari absensi
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
