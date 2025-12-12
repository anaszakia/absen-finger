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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'salary_component', 'allowance', 'deduction'
            $table->string('name'); // Nama item
            $table->string('calculation_type'); // 'fixed', 'percentage', 'per_day', 'per_hour'
            $table->decimal('amount', 15, 2); // Jumlah
            $table->integer('quantity')->default(1); // Jumlah hari/jam (untuk per_day/per_hour)
            $table->decimal('total', 15, 2); // Total = amount * quantity
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
