<?php
namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request) {
        $insumos = Insumo::with('inventario')->orderBy('nombre')->get();
        
        // --------- MOVIMIENTOS (debajo de la tabla) ---------
        $insumoSel = (int) $request->query('insumo', 0);
        $limite    = (int) $request->query('limite', 25);

        // Para el select de filtro
        $insumosFiltro = DB::table('insumos')
            ->orderBy('nombre')
            ->get(['id_insumo','nombre']);

        $movimientos = DB::table('movimientos_inventario as m')
            ->leftJoin('insumos as i',  'i.id_insumo', '=', 'm.id_insumo')
            ->leftJoin('usuarios as u', 'u.id',        '=', 'm.id_usuario')
            ->when($insumoSel > 0, fn($q) => $q->where('m.id_insumo', $insumoSel))
            ->orderByDesc('m.fecha')
            ->orderByDesc('m.id_mov')
            ->limit($limite)
            ->get([
                'm.id_mov',
                'm.fecha',
                'm.tipo',             // 'entrada' | 'salida'
                'm.cantidad',
                'm.costo_unitario',   // puede ser null en salidas
                'm.costo_total',      // ya lo tienes calculado
                'm.nota',
                'i.nombre as insumo',
                'u.nombre as usuario',
            ]);

        return view('inventario.index', compact('insumos', 'movimientos','insumosFiltro','insumoSel','limite'));
    }

    public function entrada(Request $request, $id_insumo)
    {
        $data = $request->validate([
            'cantidad' => ['required','integer','min:1','max:100000'],
            'costo_unitario' => ['required','numeric','min:0'],
            'nota' => ['nullable','string','max:2000'],
        ]);

        DB::transaction(function() use ($id_insumo, $data) {
            $inv = Inventario::where('id_insumo',$id_insumo)->lockForUpdate()->firstOrFail();

            $cantActual = (int)$inv->cantidad;
            $costoProm  = (float)$inv->costo_promedio;
            $cantEnt    = (int)$data['cantidad'];
            $costoEnt   = (float)$data['costo_unitario'];

            // PMP = (existencias * costoProm + entrada * costoEnt) / (existencias + entrada)
            $nuevoCant  = $cantActual + $cantEnt;
            $nuevoPMP   = $nuevoCant > 0
                ? (($cantActual * $costoProm) + ($cantEnt * $costoEnt)) / $nuevoCant
                : 0;

            $inv->cantidad = $nuevoCant;
            $inv->costo_promedio = round($nuevoPMP, 2);
            $inv->fecha_actualizacion = date('Y-m-d');
            $inv->save();

            MovimientoInventario::create([
                'id_insumo'     => $id_insumo,
                'id_usuario'    => Auth::id(),
                'tipo'          => 'Entrada',
                'cantidad'      => $cantEnt,
                'nota'          => $data['nota'] ?? null,
                'fecha'         => date('Y-m-d'),
                'costo_unitario'=> $costoEnt,
                'costo_total'   => $cantEnt * $costoEnt,
            ]);
        });

        return back()->with('ok','Entrada registrada.');
    }


    public function salida(Request $request, $id_insumo)
    {
        $data = $request->validate([
            'cantidad' => ['required','integer','min:1','max:100000'],
            'nota' => ['nullable','string','max:2000'],
        ]);

        DB::transaction(function() use ($id_insumo, $data) {
            $inv = Inventario::where('id_insumo',$id_insumo)->lockForUpdate()->firstOrFail();

            $cant = (int)$data['cantidad'];
            if ($cant > $inv->cantidad) abort(422, 'No hay stock suficiente.');

            $inv->cantidad -= $cant;
            $inv->fecha_actualizacion = date('Y-m-d');
            $inv->save();

            // usa PMP vigente
            $pmp = (float)$inv->costo_promedio;

            MovimientoInventario::create([
                'id_insumo'     => $id_insumo,
                'id_usuario'    => Auth::id(),
                'tipo'          => 'Salida',
                'cantidad'      => $cant,
                'nota'          => $data['nota'] ?? null,
                'fecha'         => date('Y-m-d'),
                'costo_unitario'=> $pmp,
                'costo_total'   => $cant * $pmp,
            ]);
        });

        return back()->with('ok','Salida registrada.');
    }
}
