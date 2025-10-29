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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('precio_final', 12, 2)->nullable()->after('costo_total');
            $table->integer('tiempo_estimado_dias')->nullable()->after('precio_final');
            $table->text('respuesta')->nullable()->after('tiempo_estimado_dias');
            $table->date('fecha_respuesta')->nullable()->after('respuesta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['precio_final','tiempo_estimado_dias','respuesta','fecha_respuesta']);
        });
    }
};
