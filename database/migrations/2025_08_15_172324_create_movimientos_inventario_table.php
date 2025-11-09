<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->bigIncrements('id_mov');
            $table->unsignedBigInteger('id_insumo');
            $table->unsignedBigInteger('id_usuario')->nullable(); // Usuario quién realizó el movimiento
            $table->string('tipo', 10); // Entrada | Salida
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 12, 2)->default(0); // en entradas: costo compra; en salidas: costo aplicado (PMP)
            $table->decimal('costo_total', 12, 2)->default(0);
            $table->text('nota')->nullable();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));

            $table->foreign('id_insumo')->references('id_insumo')->on('insumos');
            $table->foreign('id_usuario')->references('id')->on('usuarios');
        });
    }
    public function down(): void {
        Schema::dropIfExists('movimientos_inventario');
    }
};
