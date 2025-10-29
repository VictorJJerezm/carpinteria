<?php
namespace App\Models;

class MovimientoInventario extends BaseModel
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_mov';

    public function insumo()  
    { 
        return $this->belongsTo(Insumo::class, 'id_insumo'); 
    }

    public function usuario() 
    { 
        return $this->belongsTo(Usuario::class, 'id_usuario'); 
    }
}
