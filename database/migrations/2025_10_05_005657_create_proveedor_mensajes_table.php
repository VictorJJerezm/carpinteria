<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('proveedor_mensajes', function (Blueprint $t) {
      $t->bigIncrements('id_mensaje');
      $t->unsignedBigInteger('id_proveedor');
      $t->enum('direccion', ['saliente','entrante']); // nosotros -> proveedor | proveedor -> nosotros
      $t->string('asunto');
      $t->text('cuerpo')->nullable();                 // HTML o texto plano
      $t->string('de_email',160)->nullable();
      $t->string('para_email',160)->nullable();
      $t->text('cc')->nullable();
      $t->dateTime('fecha');                          // fecha del envio/recepción
      $t->string('message_id',255)->nullable()->unique(); // para evitar duplicados al sincronizar IMAP
      $t->string('in_reply_to',255)->nullable();
      $t->string('carpeta',80)->nullable();           // INBOX, Sent, etc (opcional)
      $t->json('adjuntos')->nullable();               // rutas locales de adjuntos guardados
      $t->foreign('id_proveedor')->references('id_proveedor')->on('proveedores')->onDelete('cascade');
      // sin timestamps
    });
  }
  public function down(): void {
    Schema::dropIfExists('proveedor_mensajes');
  }
};
