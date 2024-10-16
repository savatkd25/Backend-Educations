<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermisoRoleTable extends Migration
{
    public function up()
    {
        Schema::create('permiso_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permiso_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->primary(['permiso_id', 'role_id']);

            $table->foreign('permiso_id')->references('id')->on('permisos')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('permiso_role');
    }
}
