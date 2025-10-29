<?php
namespace App\Models;

class Insumo extends BaseModel
{
    protected $table = 'insumos';
    protected $primaryKey = 'id_insumo';

    public function inventario() 
    { 
        return $this->hasOne(Inventario::class, 'id_insumo'); 
    }

    public function movimientos() 
    { 
        return $this->hasMany(MovimientoInventario::class, 'id_insumo'); 
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_material', 'id_material', 'id_producto')
                    ->withPivot('recargo');
    }

}
