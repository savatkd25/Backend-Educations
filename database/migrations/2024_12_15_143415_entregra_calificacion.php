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
        Schema::table('entrega', function (Blueprint $table) {
            $table->decimal('calificacion', 5, 2)->nullable()->after('archivo'); // Calificación entre 0 y 100 con dos decimales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entrega', function (Blueprint $table) {
            $table->dropColumn('calificacion');
        });
    }
};
