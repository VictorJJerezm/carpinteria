<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoEstadoActualizadoMail;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim($request->query('q', ''));
        $estado   = $request->query('estado', '');
        $desde    = $request->query('desde', '');
        $hasta    = $request->query('hasta', '');
        $cliente  = (int) $request->query('cliente', 0);
        $perPage  = (int) $request->query('pp', 8);
        if ($perPage < 1 || $perPage > 50) $perPage = 8;

        $pedidos = Pedido::query()
            ->with(['usuarioCliente']) 
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q){
                $w->where('comentario','ilike',"%{$q}%")
                ->orWhere('id_pedido',$q);
            }))
            ->when($estado !== '', fn($qq) => $qq->where('estado', $estado))
            ->when($desde || $hasta, function ($qq) use ($desde, $hasta) {
                $from = $desde ? \Illuminate\Support\Carbon::parse($desde)->startOfDay() : \Illuminate\Support\Carbon::parse('1900-01-01');
                $to   = $hasta ? \Illuminate\Support\Carbon::parse($hasta)->endOfDay()   : \Illuminate\Support\Carbon::now();
                $qq->whereBetween('fecha', [$from, $to]);
            })
            ->when($cliente > 0, fn($qq) => $qq->where('id_cliente', $cliente))
            ->orderByDesc('fecha')
            ->paginate($perPage)
            ->withQueryString();

        $clientes = \DB::table('pedidos as p')
            ->join('usuarios as u','u.id','=','p.id_cliente')
            ->distinct()
            ->orderBy('u.nombre')
            ->get(['u.id','u.nombre','u.correo']);

        return view('pedidos.index', compact(
            'pedidos','clientes',
            'q','estado','desde','hasta','cliente','perPage'
        ));
    }

    public function show($id)
    {
        $p = Pedido::with([
            'cliente' => fn($q) => $q->select('id','nombre','correo'),
            'agente'  => fn($q) => $q->select('id','nombre'),
            'cotizacion' => fn($q) => $q->select('id_cotizacion','precio_final','estado'),
            'detalles' => fn($q) => $q->select('id_detalle','id_pedido','id_producto','id_material','cantidad','precio_unitario','subtotal'),
            'detalles.producto' => fn($q) => $q->select('id_producto','nombre','foto_path'),
            'detalles.material' => fn($q) => $q->select('id_insumo','nombre'),
        ])->findOrFail($id);

        return view('pedidos.show', compact('p'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $data = $request->validate([
            'estado' => ['required','in:En proceso,Terminado,Entregado']
        ]);

        $p = Pedido::findOrFail($id);
        $p->estado = $data['estado'];
        $p->save();
        $p->load(['cliente']);
        if ($p->cliente?->correo) {
            Mail::to($p->cliente->correo)->send(new PedidoEstadoActualizadoMail($p));
        }

        return back()->with('ok','Estado actualizado.');
    }
}
