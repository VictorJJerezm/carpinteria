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
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
        $table->unsignedBigInteger('id_material')->nullable()->after('id_producto');
        $table->foreign('id_material')->references('id_insumo')->on('insumos');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
        $table->dropForeign(['id_material']);
        $table->dropColumn('id_material');
        });
    }
};
