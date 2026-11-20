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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('group_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->time('hour_start'); // horario inicio
            $table->time('hour_end'); // horario fin
            $table->boolean('is_active')->default(true); // true = activo, false = inactivo
            $table->timestamps();

            $table->unique(['branch_id', 'group_type_id', 'hour_start', 'hour_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
