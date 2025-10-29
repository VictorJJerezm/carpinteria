<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $t) {
            $t->bigIncrements('id_proveedor');
            $t->string('nombre');
            $t->string('correo');
            $t->string('telefono')->nullable();
            $t->string('empresa')->nullable();
            // Etiquetas separadas por comas: "maderas, ferretería, barnices"
            $t->text('etiquetas')->nullable();
            $t->text('notas')->nullable();
            $t->boolean('activo')->default(true);
            // Sin timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
