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
        // --------- FILTROS INVENTARIO (NUEVO) ---------
        $q         = trim($request->query('q', ''));         // buscar por nombre/descr.
        $categoria = $request->query('categoria', '');       // categoría del insumo
        $estado    = $request->query('estado', '');          // Activo/Inactivo (si aplica)
        $stock     = $request->query('stock', '');           // 'bajo' | 'normal'
        $minPrecio = $request->query('min_precio', '');      // precio mínimo (insumo)
        $desde     = $request->query('desde', '');           // fecha actualización desde
        $hasta     = $request->query('hasta', '');           // fecha actualización hasta
        $pp        = (int) $request->query('pp', 8);         // por página inventario
        if ($pp < 1 || $pp > 50) $pp = 8;

        $UMBRAL_STOCK_BAJO = 5;

        // --------- LISTA PRINCIPAL: INSUMOS + INVENTARIO---------
        $insumos = Insumo::with('inventario')
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q){
                $w->where('nombre', 'ilike', "%{$q}%")
                ->orWhere('descripcion', 'ilike', "%{$q}%");
            }))
            ->when($categoria !== '', fn($qq) => $qq->where('categoria', $categoria))
            ->when($estado !== '', fn($qq) => $qq->where('estado', $estado))
            // filtros que dependen de inventario:
            ->when($stock === 'bajo',   fn($qq) => $qq->whereHas('inventario', fn($w) => $w->where('cantidad', '<',  $UMBRAL_STOCK_BAJO)))
            ->when($stock === 'normal', fn($qq) => $qq->whereHas('inventario', fn($w) => $w->where('cantidad', '>=', $UMBRAL_STOCK_BAJO)))
            // rango de precio del insumo
            ->when($minPrecio !== '', fn($qq) => $qq->where('precio', '>=', (float)$minPrecio))
            // fechas por fecha_actualizacion del inventario
            ->when($desde || $hasta, function($qq) use ($desde, $hasta) {
                $from = $desde ? \Illuminate\Support\Carbon::parse($desde)->startOfDay() : \Illuminate\Support\Carbon::parse('1900-01-01');
                $to   = $hasta ? \Illuminate\Support\Carbon::parse($hasta)->endOfDay()   : \Illuminate\Support\Carbon::now();
                $qq->whereHas('inventario', fn($w) => $w->whereBetween('fecha_actualizacion', [$from, $to]));
            })
            ->orderBy('nombre')
            ->paginate($pp)
            ->withQueryString();

        // --------- MOVIMIENTOS  ---------
        $insumoSel = (int) $request->query('insumo', 0);
        $limite = 10;

        $insumosFiltro = \DB::table('insumos')->orderBy('nombre')->get(['id_insumo','nombre']);

        $movimientos = \DB::table('movimientos_inventario as m')
            ->leftJoin('insumos as i',  'i.id_insumo', '=', 'm.id_insumo')
            ->leftJoin('usuarios as u', 'u.id',        '=', 'm.id_usuario')
            ->when($insumoSel > 0, fn($q) => $q->where('m.id_insumo', $insumoSel))
            ->orderByDesc('m.fecha')
            ->orderByDesc('m.id_mov')
            ->paginate($limite, [
                'm.id_mov',
                'm.fecha',
                'm.tipo',
                'm.cantidad',
                'm.costo_unitario',
                'm.costo_total',
                'm.nota',
                'i.nombre as insumo',
                'u.nombre as usuario',
            ], 'mov_page')
            ->withQueryString();
        
        $categorias = \DB::table('insumos')
            ->select('categoria')
            ->whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('inventario.index', compact(
            'insumos', 'movimientos', 'insumosFiltro', 'insumoSel', 'limite',
            'q', 'categoria', 'estado', 'stock',
            'minPrecio', 'desde', 'hasta', 'pp',
            'categorias'
        ));
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
