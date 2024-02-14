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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('estado')->default('Agendado');
            $table->dateTime('fecha_inicio_cita');
            $table->dateTime('fecha_fin_cita')->nullable();
            $table->text('motivo');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('empleado_id');
            $table->timestamps();
        });

        // Claves foráneas
        Schema::table('citas', function($table) {
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->foreign('empleado_id')->references('id')->on('empleados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
