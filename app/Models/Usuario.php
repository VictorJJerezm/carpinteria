<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    public $timestamps = false;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';

    protected $fillable = ['nombre','correo','contra','id_rol','activo', 'telefono'];

    protected $hidden = ['contra'];

    public function rol() 
    { 
        return $this->belongsTo(Rol::class, 'id_rol'); 
    }

    public function getAuthPassword()
    {
        return $this->contra;
    }

    // Helpers de rol
    public function esCliente()    
    { 
        return $this->rol?->slug === 'cliente'; 
    }

    public function esAdmin()      
    { 
        return $this->rol?->slug === 'admin'; 
    }
    
    public function esCarpintero() 
    { 
        return $this->rol?->slug === 'carpintero'; 
    }
}
