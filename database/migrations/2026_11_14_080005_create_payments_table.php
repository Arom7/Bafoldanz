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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('month');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['Efectivo', 'Pago por QR']);
            $table->date('paid_at');
            $table->foreignId('received_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('void_reason')->nullable();
            $table->timestamps();

            // Sin unique: la unicidad de "un pago vigente por alumno y mes"
            // se valida en PaymentService contra registros no anulados
            // (así se permite re-cobrar un mes tras anular).
            $table->index(['student_id', 'month']);
            $table->index('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
