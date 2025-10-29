<?php
namespace App\Models;

class Inventario extends BaseModel
{
    protected $table = 'inventarios';
    protected $primaryKey = 'id_inv';

    public function insumo() 
    { 
        return $this->belongsTo(Insumo::class, 'id_insumo'); 
    }

    // Helper para manejo de stock minimo
    public function getStockBajoAttribute(): bool {
        return (int)$this->cantidad < (int)$this->stock_minimo;
    }
}
