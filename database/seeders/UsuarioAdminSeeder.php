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
            ['correo' => 'admin@demo.com'], // clave única
            [
                'nombre'     => 'Admin Demo',
                'contra' => Hash::make('Admin12345'),
                'id_rol'     => $idRolAdmin,
                'activo'     => true,
            ]
        );
    }
}
