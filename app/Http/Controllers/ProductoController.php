<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    // Listado
    public function index()
    {
        $productos = Producto::orderBy('id_producto','desc')->get();
        return view('productos.index', compact('productos'));
    }

    // Form crear
    public function create()
    {
        $materiales = Insumo::where('categoria','material')->where('estado','Activo')->orderBy('nombre')->get();
        return view('productos.create', compact('materiales'));
    }

    // GUARDAR
    public function store(Request $request)
    {
         $request->validate([
            'nombre' => ['required','string','max:120'],
            'descripcion' => ['nullable','string'],
            'precio_estimado' => ['nullable','numeric','min:0'],
            'estado' => ['nullable','in:Activo,Inactivo'],
            'foto' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:4096'],
            // materiales
            'materiales' => ['array'],
            'materiales.*' => ['integer','exists:insumos,id_insumo'],
            'recargos' => ['array'],
        ]);

        $data = $request->only('nombre','descripcion','precio_estimado','estado');

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('productos','public');
        }

        $producto = Producto::create($data);

        // sincronizar materiales
        $this->syncMateriales($producto, $request);

        return redirect()->route('productos.index')->with('ok','Producto creado.');
    }

    // Form editar
    public function edit(Producto $producto)
    {
        $materiales = Insumo::where('categoria','material')->where('estado','Activo')->orderBy('nombre')->get();
        $producto->load('materiales');
        return view('productos.edit', compact('producto','materiales'));
    }

    // ACTUALIZAR (con reemplazo de imagen)
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => ['required','string','max:120'],
            'descripcion' => ['nullable','string'],
            'precio_estimado' => ['nullable','numeric','min:0'],
            'estado' => ['nullable','in:Activo,Inactivo'],
            'foto' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:4096'],
            // materiales
            'materiales' => ['array'],
            'materiales.*' => ['integer','exists:insumos,id_insumo'],
            'recargos' => ['array'],
        ]);

        $data = $request->only('nombre','descripcion','precio_estimado','estado');

        if ($request->hasFile('foto')) {
            if ($producto->foto_path && \Storage::disk('public')->exists($producto->foto_path)) {
                \Storage::disk('public')->delete($producto->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('productos','public');
        }

        $producto->update($data);

        $this->syncMateriales($producto, $request);

        return redirect()->route('productos.index')->with('ok','Producto actualizado.');
    }

    // Eliminar
    public function destroy(Producto $producto)
    {
        if ($producto->foto_path && Storage::disk('public')->exists($producto->foto_path)) {
            Storage::disk('public')->delete($producto->foto_path);
        }
        $producto->delete();

        return back()->with('ok', 'Producto eliminado.');
    }

    // Sincronización de materiales + recargos
    protected function syncMateriales(Producto $producto, Request $request): void
    {
        $ids = $request->input('materiales', []);
        $rec = $request->input('recargos', []);

        $sync = [];
        foreach ($ids as $idMat) {
            $r = isset($rec[$idMat]) ? (float)$rec[$idMat] : 0;
            $sync[$idMat] = ['recargo' => $r];
        }
        $producto->materiales()->sync($sync);
    }
}
