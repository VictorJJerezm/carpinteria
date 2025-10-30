<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $idRolAdmin = DB::table('roles')->where('slug','admin')->value('id');

        DB::table('usuarios')->updateOrInsert(
            ['correo' => 'vjerezmijangos@gmail.com'], // clave única
            [
                'nombre'     => 'Victor Jerez',
                'contra' => Hash::make('VJJ291002*'),
                'id_rol'     => $idRolAdmin,
                'activo'     => true,
                'telefono'   => '57181667',
            ]
        );
    }
}
