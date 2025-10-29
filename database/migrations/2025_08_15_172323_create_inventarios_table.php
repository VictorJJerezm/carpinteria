<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->bigIncrements('id_inv');
            $table->unsignedBigInteger('id_insumo');
            $table->integer('cantidad')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->decimal('costo_promedio', 12, 2)->default(0);
            $table->date('fecha_actualizacion')->default(DB::raw('CURRENT_DATE'));

            $table->foreign('id_insumo')->references('id_insumo')->on('insumos');
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventarios');
    }
};
