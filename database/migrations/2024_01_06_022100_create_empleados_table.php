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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->string('nombre', 64);
            $table->string('apellido', 64);
            $table->string('nombre_completo')->virtualAs('concat(nombre, \' \', apellido)');
            $table->integer('edad');
            $table->string('password');
            $table->string('correo')->unique();
            $table->string('direccion')->nullable();
            $table->string('telefono')->unique();
            $table->date('fecha_de_contratacion');
            $table->string('contacto_opcional')->nullable();
            $table->integer('activo')->default(1);
            $table->timestamps();
        });

        // Claves foráneas
        Schema::table('empleados', function($table) {
            $table->foreign('grupo_id')->references('id')->on('grupos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
