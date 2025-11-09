<?php
namespace App\Models;


class Cotizacion extends BaseModel
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';
    protected $casts = [
        'requiere_confirmacion_medidas' => 'boolean',
    ];

    public function cliente() 
    { 
        return $this->belongsTo(Usuario::class, 'id_cliente'); 
    }
    
    public function usuario() 
    { 
        return $this->belongsTo(Usuario::class, 'id_usuario'); 
    }
    
    public function detalles()
    { 
        return $this->hasMany(DetalleCotizacion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function pedido()
    { 
        return $this->hasOne(Pedido::class, 'id_cotizacion', 'id_cotizacion'); 
    }

    public function usuarioCliente()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id');
    }
}
