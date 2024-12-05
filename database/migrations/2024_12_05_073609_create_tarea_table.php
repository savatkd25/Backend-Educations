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
        Schema::create('tarea', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('titulo', 255);
            $table->text('descripcion');
            $table->date('fecha_entrega');
            $table->unsignedBigInteger('curso_id');
            $table->foreign('curso_id')->references('id')->on('curso')->onDelete('cascade');
            $table->string('archivo', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarea');
    }
};
