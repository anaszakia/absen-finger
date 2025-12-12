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
        Schema::create('allowances', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama tunjangan: Lembur, Bonus Kehadiran, dll
            $table->string('type'); // 'per_hour', 'per_day', 'fixed', 'percentage'
            $table->decimal('amount', 15, 2)->default(0); // Jumlah tunjangan
            $table->decimal('percentage', 5, 2)->default(0); // Persentase dari gaji
            $table->boolean('requires_approval')->default(false); // Butuh persetujuan
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
        Schema::dropIfExists('allowances');
    }
};
