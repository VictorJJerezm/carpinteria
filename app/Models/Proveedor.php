<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre','correo','telefono','empresa','etiquetas','notas','activo'
    ];

    // Helper para mostrar etiquetas como array
    public function getEtiquetasArrayAttribute()
    {
        return collect(preg_split('/\s*,\s*/', (string)$this->etiquetas, -1, PREG_SPLIT_NO_EMPTY))
               ->map(fn($e) => trim($e))->unique()->values();
    }

    // Scopes útiles
    public function scopeActivos($q) { return $q->where('activo', true); }
}
