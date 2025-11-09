<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cotizacion;
use App\Models\Pedido;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Rango por defecto: últimos 90 días
        $hasta = $request->query('hasta', Carbon::today()->toDateString());
        $desde = $request->query('desde', Carbon::today()->subDays(89)->toDateString());

        // --- Cotizaciones por estado
        $cotEstados = Cotizacion::whereBetween('fecha', [$desde, $hasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $cPend  = $cotEstados['Pendiente']  ?? 0;
        $cResp  = $cotEstados['Respondida'] ?? 0;
        $cApr   = $cotEstados['Aprobada']   ?? 0;
        $cRech  = $cotEstados['Rechazada']  ?? 0;
        $cCanc  = $cotEstados['Cancelada']  ?? 0;
        $cTotal = $cPend + $cResp + $cApr + $cRech + $cCanc;

        // Tasa de aprobación sobre respondidas (evita división por 0)
        $tasaAprob = $cResp + $cRech + $cApr > 0
            ? round(($cApr / max(1, $cResp + $cRech + $cApr)) * 100, 1)
            : 0;

        // --- Pedidos: ingresos por mes y por estado
        $ingresosMensuales = Pedido::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("to_char(date_trunc('month', fecha), 'YYYY-MM') as mes")
            ->selectRaw('SUM(total) as total')
            ->groupBy(\DB::raw("date_trunc('month', fecha)"))
            ->orderBy(\DB::raw("date_trunc('month', fecha)"))
            ->get();

        $ingresosTotales = Pedido::whereBetween('fecha', [$desde, $hasta])->sum('total');

        $pedEstados = Pedido::whereBetween('fecha', [$desde, $hasta])
            ->select('estado', \DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total','estado');

        $pProc = $pedEstados['En proceso'] ?? 0;
        $pTerm = $pedEstados['Terminado']  ?? 0;
        $pEnt  = $pedEstados['Entregado']  ?? 0;
        $pTot  = $pProc + $pTerm + $pEnt;

        // --- Top productos (por subtotal del pedido_detalle)
        $topProductos = DB::table('pedido_detalle as d')
            ->join('pedidos as pe','pe.id_pedido','=','d.id_pedido')
            ->join('productos as p','p.id_producto','=','d.id_producto')
            ->whereBetween('pe.fecha', [$desde,$hasta])
            ->select('p.nombre', DB::raw('SUM(d.cantidad) as cantidad'), DB::raw('SUM(d.subtotal) as subtotal'))
            ->groupBy('p.nombre')
            ->orderByDesc('subtotal')
            ->limit(5)
            ->get();

        // --- Top materiales
        $topMateriales = DB::table('pedido_detalle as d')
            ->join('pedidos as pe','pe.id_pedido','=','d.id_pedido')
            ->join('insumos as i','i.id_insumo','=','d.id_material')
            ->whereBetween('pe.fecha', [$desde,$hasta])
            ->whereNotNull('d.id_material')
            ->select('i.nombre', DB::raw('SUM(d.cantidad) as cantidad'), DB::raw('SUM(d.subtotal) as subtotal'))
            ->groupBy('i.nombre')
            ->orderByDesc('subtotal')
            ->limit(5)
            ->get();

        // --- Clientes con más cotizaciones
        $topClientes = DB::table('cotizaciones as c')
            ->join('usuarios as u','u.id','=','c.id_cliente')
            ->whereBetween('c.fecha', [$desde,$hasta])
            ->select('u.nombre','u.correo', DB::raw('COUNT(*) as total'))
            ->groupBy('u.id','u.nombre','u.correo')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Datos para Chart.js
        $meses = $ingresosMensuales->pluck('mes');
        $ingresos = $ingresosMensuales->pluck('total');

        return view('reportes.index', compact(
            'desde','hasta',
            'cPend','cResp','cApr','cRech','cCanc','cTotal','tasaAprob',
            'pProc','pTerm','pEnt','pTot','ingresosTotales',
            'meses','ingresos',
            'topProductos','topMateriales','topClientes'
        ));
    }

    // (Opcional) Export CSV simple de ingresos por mes
    public function export(Request $request)
    {
        $hasta = $request->query('hasta', Carbon::today()->toDateString());
        $desde = $request->query('desde', Carbon::today()->subDays(89)->toDateString());

        $rows = Pedido::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("to_char(date_trunc('month', fecha), 'YYYY-MM') as mes")
            ->selectRaw('SUM(total) as total')
            ->groupBy(DB::raw("date_trunc('month', fecha)"))
            ->orderBy(DB::raw("date_trunc('month', fecha)"))
            ->get();

        $csv = "Mes,Ingresos\n";
        foreach ($rows as $r) $csv .= "{$r->mes},".number_format($r->total,2,'.','')."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ingresos_mensuales.csv"',
        ]);
    }

    public function materiales(Request $r)
    {
        $hasta = $r->query('hasta', Carbon::today()->toDateString());
        $desde = $r->query('desde', Carbon::today()->subDays(89)->toDateString());

        // Top materiales (solo los que se eligieron como material en pedidos)
        $top = DB::table('pedido_detalle as d')
            ->join('pedidos as pe','pe.id_pedido','=','d.id_pedido')
            ->join('insumos as i','i.id_insumo','=','d.id_material')
            ->whereBetween('pe.fecha', [$desde,$hasta])
            ->whereNotNull('d.id_material')
            ->select('i.id_insumo','i.nombre',
                     DB::raw('SUM(d.cantidad) as cantidad'),
                     DB::raw('SUM(d.subtotal) as subtotal'))
            ->groupBy('i.id_insumo','i.nombre')
            ->orderByDesc('subtotal')
            ->get();

        // Serie mensual de consumo (Q)
        $serie = \DB::table('pedido_detalle as d')
            ->join('pedidos as pe','pe.id_pedido','=','d.id_pedido')
            ->whereBetween('pe.fecha', [$desde,$hasta])
            ->whereNotNull('d.id_material')
            ->selectRaw("to_char(date_trunc('month', pe.fecha), 'YYYY-MM') as mes")
            ->selectRaw('SUM(d.subtotal) as total')
            ->groupBy(DB::raw("date_trunc('month', pe.fecha)"))
            ->orderBy(DB::raw("date_trunc('month', pe.fecha)"))
            ->get();

        $meses = $serie->pluck('mes');
        $totMes = $serie->pluck('total');

        return view('reportes.materiales', compact('desde','hasta','top','meses','totMes'));
    }

    public function insumos(Request $r)
    {
        $rows = \DB::table('insumos as i')
            ->leftJoin('inventarios as inv', 'inv.id_insumo', '=', 'i.id_insumo')
            ->select(
                'i.id_insumo',
                'i.nombre',
                'i.descripcion',
                \DB::raw('COALESCE(inv.cantidad, 0) as stock'),
                \DB::raw('COALESCE(inv.stock_minimo, 0) as stock_minimo'),
                \DB::raw('COALESCE(inv.costo_promedio, 0) as costo_promedio'),
                \DB::raw("to_char(inv.fecha_actualizacion, 'YYYY-MM-DD') as fecha_actualizacion")
            )
            ->orderBy('i.nombre')
            ->get();

        // Calcula banderas de stock
        $insumos = $rows->map(function ($x) {
            $x->estado_stock = ($x->stock_minimo > 0 && $x->stock < $x->stock_minimo) ? 'Bajo' : 'OK';
            $x->porcentaje   = ($x->stock_minimo > 0) ? round(($x->stock / $x->stock_minimo) * 100, 0) : null;
            return $x;
        });

        return view('reportes.insumos', compact('insumos'));
    }


    /** ====== CLIENTES (actividad y valor) ====== */
    public function clientes(Request $r)
    {
        $hasta = $r->query('hasta', Carbon::today()->toDateString());
        $desde = $r->query('desde', Carbon::today()->subDays(89)->toDateString());

        // Subconsultas por rango
        $cotSub = DB::raw("
            (select id_cliente,
                    count(*) as cot_total,
                    sum(case when estado='Aprobada' then 1 else 0 end) as cot_aprobadas
             from cotizaciones
             where fecha between '{$desde}' and '{$hasta}'
             group by id_cliente) as c
        ");

        $pedSub = DB::raw("
            (select id_cliente,
                    count(*) as pedidos,
                    sum(total) as monto
             from pedidos
             where fecha between '{$desde}' and '{$hasta}'
             group by id_cliente) as p
        ");

        $clientes = DB::table('usuarios as u')
            ->join('roles as r','r.id','=','u.id_rol')       // si tu tabla de roles se llama distinto, ajusta
            ->leftJoin($cotSub, 'c.id_cliente', '=', 'u.id')
            ->leftJoin($pedSub, 'p.id_cliente', '=', 'u.id')
            ->where('r.slug','cliente')
            ->select('u.id','u.nombre','u.correo',
                     DB::raw('COALESCE(c.cot_total,0) as cot_total'),
                     DB::raw('COALESCE(c.cot_aprobadas,0) as cot_aprobadas'),
                     DB::raw('COALESCE(p.pedidos,0) as pedidos'),
                     DB::raw('COALESCE(p.monto,0) as monto'))
            ->orderByDesc('monto')
            ->get();

        return view('reportes.clientes', compact('desde','hasta','clientes'));
    }

    /** ====== EXPORTS ====== */
    public function exportMateriales(Request $r)
    {
        $hasta = $r->query('hasta', Carbon::today()->toDateString());
        $desde = $r->query('desde', Carbon::today()->subDays(89)->toDateString());

        $rows = DB::table('pedido_detalle as d')
            ->join('pedidos as pe','pe.id_pedido','=','d.id_pedido')
            ->join('insumos as i','i.id_insumo','=','d.id_material')
            ->whereBetween('pe.fecha', [$desde,$hasta])
            ->whereNotNull('d.id_material')
            ->select('i.nombre',
                     DB::raw('SUM(d.cantidad) as cantidad'),
                     DB::raw('SUM(d.subtotal) as subtotal'))
            ->groupBy('i.nombre')
            ->orderByDesc('subtotal')
            ->get();

        $csv = "Material,Cantidad,Subtotal\n";
        foreach ($rows as $r) $csv .= "{$r->nombre},{$r->cantidad},".number_format($r->subtotal,2,'.','')."\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="materiales_consumo.csv"',
        ]);
    }

    public function exportInsumos()
    {
        $rows = \DB::table('insumos as i')
            ->leftJoin('inventarios as inv', 'inv.id_insumo', '=', 'i.id_insumo')
            ->select(
                'i.nombre',
                \DB::raw('COALESCE(inv.cantidad, 0) as stock'),          // cantidad
                \DB::raw('COALESCE(inv.stock_minimo, 0) as stock_minimo'),
                \DB::raw('COALESCE(inv.costo_promedio, 0) as costo_promedio')
            )
            ->orderBy('i.nombre')
            ->get();

        // CSV
        $csv = "Insumo,Stock,Stock minimo,Estado,% sobre minimo,Costo promedio,Costo total\n";
        foreach ($rows as $r) {
            $estado = ($r->stock_minimo > 0 && $r->stock < $r->stock_minimo) ? 'Bajo' : 'OK';
            $pct    = ($r->stock_minimo > 0) ? round(($r->stock / $r->stock_minimo) * 100) . '%' : '';
            $costoTotal = (float)$r->stock * (float)$r->costo_promedio;

            $csv .= sprintf(
                "%s,%d,%d,%s,%s,%.2f,%.2f\n",
                $r->nombre,
                (int)$r->stock,
                (int)$r->stock_minimo,
                $estado,
                $pct,
                (float)$r->costo_promedio,
                $costoTotal
            );
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="insumos_stock.csv"',
        ]);
    }

    public function exportClientes(Request $r)
    {
        $hasta = $r->query('hasta', Carbon::today()->toDateString());
        $desde = $r->query('desde', Carbon::today()->subDays(89)->toDateString());

        $rows = DB::table('usuarios as u')
            ->join('roles as r','r.id','=','u.id_rol')
            ->leftJoin(DB::raw("
                (select id_cliente, count(*) cot_total,
                        sum(case when estado='Aprobada' then 1 else 0 end) cot_aprobadas
                 from cotizaciones
                 where fecha between '{$desde}' and '{$hasta}'
                 group by id_cliente) c
            "),'c.id_cliente','=','u.id')
            ->leftJoin(DB::raw("
                (select id_cliente, count(*) pedidos, sum(total) monto
                 from pedidos
                 where fecha between '{$desde}' and '{$hasta}'
                 group by id_cliente) p
            "),'p.id_cliente','=','u.id')
            ->where('r.slug','cliente')
            ->select('u.nombre','u.correo',
                     DB::raw('COALESCE(c.cot_total,0) as cot_total'),
                     DB::raw('COALESCE(c.cot_aprobadas,0) as cot_aprobadas'),
                     DB::raw('COALESCE(p.pedidos,0) as pedidos'),
                     DB::raw('COALESCE(p.monto,0) as monto'))
            ->orderByDesc('monto')->get();

        $csv = "Cliente,Correo,Cotizaciones,Aprobadas,Pedidos,Monto\n";
        foreach ($rows as $r) {
            $csv .= "{$r->nombre},{$r->correo},{$r->cot_total},{$r->cot_aprobadas},{$r->pedidos},".number_format($r->monto,2,'.','')."\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clientes_resumen.csv"',
        ]);
    }    
}