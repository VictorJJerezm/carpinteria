<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoClienteYProductosSeeder extends Seeder
{
    public function run(): void
    {
        $idClienteRol = DB::table('roles')->where('slug','cliente')->value('id');

        // Cliente demo
        DB::table('usuarios')->updateOrInsert(
            ['correo' => 'cliente@demo.com'],
            [
                'nombre'     => 'Cliente Demo',
                'contra' => Hash::make('Cliente12345'),
                'id_rol'     => $idClienteRol,
                'activo'     => true,
            ]
        );

        // Productos demo
        if (DB::table('productos')->count() === 0) {
            DB::table('productos')->insert([
                ['nombre'=>'Mesa de pino','descripcion'=>'Mesa 4 puestos','precio_estimado'=>1200,'estado'=>'Activo','foto_path'=>null],
                ['nombre'=>'Silla de cedro','descripcion'=>'Silla clásica','precio_estimado'=>450,'estado'=>'Activo','foto_path'=>null],
                ['nombre'=>'Estante arce','descripcion'=>'3 niveles','precio_estimado'=>800,'estado'=>'Activo','foto_path'=>null],
            ]);
        }
    }
}
