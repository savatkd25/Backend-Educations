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
        Schema::create('curso', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nombre', 255);
            $table->string('codigo', 10);
            $table->string('descripcion', 255);
            $table->integer('creditos');
            $table->integer('horas');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            //fk a asignacion
            $table->unsignedBigInteger('asignacion_id');
            $table->foreign('asignacion_id')->references('id')->on('asignaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};
