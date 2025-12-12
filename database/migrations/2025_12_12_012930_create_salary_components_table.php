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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama komponen: Gaji Pokok, Tunjangan Transport, dll
            $table->string('type'); // 'fixed' atau 'percentage'
            $table->decimal('amount', 15, 2)->default(0); // Jumlah nominal
            $table->decimal('percentage', 5, 2)->default(0); // Persentase dari gaji pokok
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
        Schema::dropIfExists('salary_components');
    }
};
