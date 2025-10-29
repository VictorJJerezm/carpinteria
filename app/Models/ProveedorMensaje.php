<?php

namespace App\Models;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Model;

class ProveedorMensaje extends Model
{
    protected $table = 'proveedor_mensajes';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor','direccion','asunto','cuerpo','de_email','para_email','cc',
        'fecha','message_id','in_reply_to','carpeta','adjuntos'
    ];

    protected $casts = ['fecha' => 'datetime','adjuntos' => 'array'];

    public function proveedor() {
        return $this->belongsTo(Proveedor::class,'id_proveedor','id_proveedor');
    }
}
