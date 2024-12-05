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
        Schema::create('entrega', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('archivo', 255);
            $table->text('comentarios')->nullable();
            $table->unsignedBigInteger('tarea_id');
            $table->unsignedBigInteger('estudiante_id');
            $table->foreign('tarea_id')->references('id')->on('tarea')->onDelete('cascade');
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('archivo', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega');
    }
};
