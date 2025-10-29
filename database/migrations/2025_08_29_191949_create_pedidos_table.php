<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id_pedido');
            $table->unsignedBigInteger('id_cotizacion')->unique();
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_agente')->nullable(); // quien atiende (carpintero/admin)
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_entrega_estimada')->nullable();
            $table->decimal('total', 12, 2);
            $table->string('estado', 30)->default('En proceso'); // En proceso | Terminado | Entregado
            $table->text('nota')->nullable();

            $table->foreign('id_cotizacion')->references('id_cotizacion')->on('cotizaciones')->onDelete('cascade');
            $table->foreign('id_cliente')->references('id')->on('usuarios');
            $table->foreign('id_agente')->references('id')->on('usuarios');
        });
    }
    public function down(): void {
        Schema::dropIfExists('pedidos');
    }
};
