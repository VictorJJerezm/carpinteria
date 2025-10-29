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
        Schema::create('usuarios', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('nombre', 100);
        $table->string('correo', 150)->unique();
        $table->string('contra', 255);
        $table->unsignedSmallInteger('id_rol');
        $table->boolean('activo')->default(true);

        $table->foreign('id_rol')->references('id')->on('roles');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
