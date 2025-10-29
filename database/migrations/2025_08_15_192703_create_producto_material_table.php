<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('producto_material', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('id_producto');
        $table->unsignedBigInteger('id_material'); // insumos.id_insumo
        $table->decimal('recargo', 12, 2)->default(0); // Q adicional por material
        $table->unique(['id_producto','id_material']);

        $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
        $table->foreign('id_material')->references('id_insumo')->on('insumos')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_material');
    }
};
