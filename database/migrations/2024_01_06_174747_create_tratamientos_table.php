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
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diagnostico_id');
            $table->string('tipo_tratamiento');
            $table->string('medicamento')->nullable();
            $table->string('dosis')->nullable();
            $table->text('procedimiento');
            $table->string('notas');
            $table->timestamps();
        });

        // Claves foráneas
        Schema::table('tratamientos', function($table) {
            $table->foreign('diagnostico_id')->references('id')->on('diagnosticos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
