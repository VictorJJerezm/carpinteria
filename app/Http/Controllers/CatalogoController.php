<?php
namespace App\Http\Controllers;

use App\Models\Producto;

class CatalogoController extends Controller
{
    public function index()
    {
        $productos = \App\Models\Producto::where('estado','Activo')
            ->with(['materiales:id_insumo,nombre'])
            ->orderBy('nombre')
            ->get(['id_producto','nombre','descripcion','precio_estimado','foto_path','estado']);

        return view('catalogo.index', compact('productos'));
    }
}
