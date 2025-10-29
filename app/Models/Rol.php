<?php
namespace App\Models;

class Rol extends BaseModel
{
    protected $table = 'roles';
    public function usuarios() 
    { 
        return $this->hasMany(Usuario::class, 'id_rol'); 
    }
}
