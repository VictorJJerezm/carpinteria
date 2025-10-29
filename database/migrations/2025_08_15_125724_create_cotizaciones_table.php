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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->bigIncrements('id_cotizacion');
            $table->unsignedBigInteger('id_cliente'); 
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->date('fecha');
            $table->decimal('costo_total', 12, 2)->default(0);
            $table->string('estado', 20)->default('Pendiente');
            $table->text('comentario')->nullable();

            $table->foreign('id_cliente')->references('id')->on('usuarios');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
