<?php
namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumoController extends Controller
{
    public function index() {
        $insumos = Insumo::with('inventario')->orderBy('id_insumo','desc')->get();
        return view('insumos.index', compact('insumos'));
    }

    public function create() { return view('insumos.create'); }


    public function store(Request $request) {
        $val = $request->validate([
            'nombre'        => ['required','string','max:120'],
            'descripcion'   => ['nullable','string'],
            'tipo_material' => ['nullable','string','max:50'],
            'largo'         => ['nullable','numeric','min:0'],
            'alto'          => ['nullable','numeric','min:0'],
            'ancho'         => ['nullable','numeric','min:0'],
            'precio'        => ['required','numeric','min:0'],
            'estado'        => ['required','in:Activo,Inactivo'],
            'stock_minimo'  => ['nullable','integer','min:0'],
            'categoria'     => ['nullable','string','max:20'],
            'unidad'        => ['nullable','string','max:20'],
        ]);

        $insumoData = collect($val)->except(['stock_minimo'])->toArray();
        $stockMinimo = (int)($val['stock_minimo'] ?? 5);

        \DB::transaction(function() use ($insumoData, $stockMinimo) {
            $insumo = Insumo::create($insumoData);

            Inventario::create([
                'id_insumo'          => $insumo->id_insumo,
                'cantidad'           => 0,
                'stock_minimo'       => $stockMinimo,
                'fecha_actualizacion'=> date('Y-m-d'),
            ]);
        });

        return redirect()->route('insumos.index')->with('ok','Insumo creado.');
    }

    public function edit(Insumo $insumo) {
        $insumo->load('inventario');
        return view('insumos.edit', compact('insumo'));
    }

    public function update(Request $request, Insumo $insumo) {
        $val = $request->validate([
            'nombre'        => ['required','string','max:120'],
            'descripcion'   => ['nullable','string'],
            'tipo_material' => ['nullable','string','max:50'],
            'largo'         => ['nullable','numeric','min:0'],
            'alto'          => ['nullable','numeric','min:0'],
            'ancho'         => ['nullable','numeric','min:0'],
            'precio'        => ['required','numeric','min:0'],
            'estado'        => ['required','in:Activo,Inactivo'],
            'stock_minimo'  => ['nullable','integer','min:0'],
            'categoria'     => ['nullable','string','max:20'],
            'unidad'        => ['nullable','string','max:20'],
        ]);

        $insumoData = collect($val)->except(['stock_minimo'])->toArray();
        $stockMinimo = $val['stock_minimo'] ?? null;

        \DB::transaction(function() use ($insumo, $insumoData, $stockMinimo) {
            $insumo->update($insumoData);

            if ($insumo->inventario) {
                $payloadInv = ['fecha_actualizacion' => date('Y-m-d')];
                if (!is_null($stockMinimo)) {
                    $payloadInv['stock_minimo'] = (int)$stockMinimo;
                }
                $insumo->inventario->update($payloadInv);
            }
        });

        return redirect()->route('insumos.index')->with('ok','Insumo actualizado.');
    }

    public function destroy(Insumo $insumo) {
        DB::transaction(function() use ($insumo) {
            if ($insumo->inventario) $insumo->inventario->delete();
            $insumo->delete();
        });
        return back()->with('ok','Insumo eliminado.');
    }
}
