<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('insumos', function (Blueprint $table) {
            $table->bigIncrements('id_insumo');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('tipo_material', 50)->nullable();
            $table->decimal('largo', 10, 2)->nullable();
            $table->decimal('alto', 10, 2)->nullable();
            $table->decimal('ancho', 10, 2)->nullable();
            $table->string('categoria', 20)->default('material');
            $table->string('unidad', 20)->default('pieza');          
            $table->decimal('precio', 12, 2)->default(0);
            $table->string('estado', 20)->default('Activo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('insumos');
    }
};
