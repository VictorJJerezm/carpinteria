<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->upsert(
            [
                ['nombre' => 'Administrador', 'slug' => 'admin',      'activo' => true],
                ['nombre' => 'Carpintero',   'slug' => 'carpintero', 'activo' => true],
                ['nombre' => 'Cliente',      'slug' => 'cliente',    'activo' => true],
            ],
            ['slug'],              // columna única para resolver conflictos
            ['nombre','activo']    // columnas que se actualizarán si ya existe
        );
    }
}
