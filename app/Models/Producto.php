<?php
namespace App\Models;


class Producto extends BaseModel
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_path ? asset('storage/'.$this->foto_path) : null;
    }   

    public function materiales()
    {
        return $this->belongsToMany(Insumo::class, 'producto_material', 'id_producto', 'id_material')
                    ->withPivot('recargo');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCotizacion::class, 'id_producto', 'id_producto');
    }
}
