<?php
namespace App\Models;

class Pedido extends BaseModel
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_cotizacion','id_cliente','id_agente','fecha',
        'fecha_entrega_estimada','total','estado','nota'
    ];

    public function cotizacion()
    {
         return $this->belongsTo(Cotizacion::class, 'id_cotizacion', 'id_cotizacion'); 
    }

    public function cliente()
    { 
        return $this->belongsTo(Usuario::class, 'id_cliente'); 
    }

    public function agente()
    { 
        return $this->belongsTo(Usuario::class, 'id_agente'); 
    }
    
    public function detalles()
    { 
        return $this->hasMany(PedidoDetalle::class, 'id_pedido', 'id_pedido'); 
    }

    public function usuarioCliente()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id');
    }
}
