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
        $estado = $request->query('estado');
        $q = Pedido::with([
            'cliente' => fn($q) => $q->select('id','nombre','correo'),
            'agente'  => fn($q) => $q->select('id','nombre'),
            'detalles' => fn($q) => $q->select('id_detalle','id_pedido','id_producto','id_material','cantidad','precio_unitario','subtotal'),
            'detalles.producto' => fn($q) => $q->select('id_producto','nombre','foto_path'),
            'detalles.material' => fn($q) => $q->select('id_insumo','nombre'),
        ])->orderBy('id_pedido', 'desc');

        if ($estado) $q->where('estado', $estado);

        $pedidos = $q->get();
        return view('pedidos.index', compact('pedidos','estado'));
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
