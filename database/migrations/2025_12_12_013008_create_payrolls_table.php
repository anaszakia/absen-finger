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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('period'); // Format: YYYY-MM (contoh: 2025-12)
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('basic_salary', 15, 2); // Gaji pokok
            $table->integer('total_days')->default(0); // Total hari kerja
            $table->integer('present_days')->default(0); // Hari hadir
            $table->integer('late_days')->default(0); // Hari terlambat
            $table->integer('absent_days')->default(0); // Hari tidak hadir
            $table->decimal('total_allowances', 15, 2)->default(0); // Total tunjangan
            $table->decimal('total_deductions', 15, 2)->default(0); // Total potongan
            $table->decimal('gross_salary', 15, 2); // Gaji kotor
            $table->decimal('net_salary', 15, 2); // Gaji bersih (take home pay)
            $table->string('status')->default('draft'); // draft, approved, paid
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'period']); // Satu employee satu period
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
