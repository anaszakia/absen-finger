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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action')->nullable();
            $table->string('controller')->nullable();
            $table->string('route')->nullable();
            $table->string('method', 10);
            $table->text('url')->nullable();
            $table->ipAddress('ip');
            $table->text('user_agent')->nullable();
            $table->integer('status_code')->nullable();
            $table->json('request_data')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
            
            $table->index(['user_id', 'performed_at']);
            $table->index(['action', 'performed_at']);
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
