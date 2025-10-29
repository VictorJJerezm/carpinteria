<?php
namespace App\Models;

class PedidoDetalle extends BaseModel
{
    protected $table = 'pedido_detalle';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido','id_producto','id_material','cantidad','precio_unitario','subtotal'
    ];

    public function pedido()
    { 
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function producto()
    { 
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto'); 
    }

    public function material()
    { 
        return $this->belongsTo(Insumo::class, 'id_material', 'id_insumo'); 
    }
}
