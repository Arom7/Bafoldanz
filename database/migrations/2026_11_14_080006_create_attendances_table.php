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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('date');
            $table->enum('code', ['P', 'F', 'J', 'R', 'H', 'S']);
            // enum BD: Presente | Falta | Justificada | Reposición | Feriado | Suspendida por academia
            $table->timestamps();

            $table->unique(['student_id', 'date']);

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
