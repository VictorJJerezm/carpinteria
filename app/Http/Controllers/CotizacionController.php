<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Support\Facades\Mail;
use App\Mail\CotizacionAprobadaMail;
use App\Mail\CotizacionRechazadaMail;

class CotizacionController extends Controller
{
    // Formulario para el cliente
    public function index( Request $request )
    {
        
        $seleccionado = (int) $request->query('producto', 0);

        $productos = Producto::where('estado','Activo')
            ->with(['materiales' => function($q){
                $q->select('insumos.id_insumo','insumos.nombre'); // campos mínimos
            }])
            ->orderBy('nombre')
            ->get(['id_producto','nombre','precio_estimado', 'foto_path']);

        return view('cotizaciones.crear', compact('productos', 'seleccionado'));
    }

    // Guardar cotización (cabecera + detalle)
    public function store(Request $request)
    {
        $request->validate([
            'id_producto' => ['required','exists:productos,id_producto'],
            'id_material' => ['required','exists:insumos,id_insumo'],
            'cantidad'    => ['required','integer','min:1','max:9999'],
            'comentario'  => ['nullable','string','max:2000'],
            'requiere_confirmacion_medidas' => ['required','in:0,1'],
        ]);

        $user = \Auth::user();
        $producto = Producto::with(['materiales' => function($q){
            $q->select('insumos.id_insumo','insumos.nombre');
        }])->findOrFail($request->id_producto);

        // Validar que el material pertenezca al producto
        $material = $producto->materiales->firstWhere('id_insumo', (int)$request->id_material);
        if (!$material) {
            return back()->withErrors('El material seleccionado no está disponible para este producto.')->withInput();
        }

        $precioBase = (float)($producto->precio_estimado ?? 0);
        $recargo    = (float)($material->pivot->recargo ?? 0);
        $precioUnit = $precioBase + $recargo;
        $cantidad   = (int)$request->cantidad;
        $subtotal   = $precioUnit * $cantidad;

        \DB::transaction(function() use ($user, $producto, $material, $precioUnit, $cantidad, $subtotal, $request) {
            $cab = Cotizacion::create([
                'id_cliente'  => $user->id,
                'id_usuario'  => null,
                'fecha'       => date('Y-m-d'),
                'costo_total' => $subtotal,
                'estado'      => 'Pendiente',
                'comentario'  => $request->comentario,
                'requiere_confirmacion_medidas' => (bool)$request->requiere_confirmacion_medidas,
            ]);

            DetalleCotizacion::create([
                'id_cotizacion'  => $cab->id_cotizacion,
                'id_producto'    => $producto->id_producto,
                'id_material'    => $material->id_insumo,
                'cantidad'       => $cantidad,
                'precio_unitario'=> $precioUnit,
                'subtotal'       => $subtotal,
            ]);
        });

        return redirect()->route('cliente.cotizar.index')->with('ok', 'Cotización enviada.');
    }

    public function mis()
    {
        $cotizaciones = Cotizacion::with([
            'pedido' => fn($q) => $q->select('id_pedido','id_cotizacion','estado'),
            'detalles' => fn($q) => $q->select('id_detalle','id_cotizacion','id_producto','id_material','cantidad','precio_unitario','subtotal'),
            'detalles.producto' => fn($q) => $q->select('id_producto','nombre','foto_path'),
            'detalles.material' => fn($q) => $q->select('id_insumo','nombre'),
        ])
        ->where('id_cliente', \Auth::id())
        ->orderBy('id_cotizacion','desc')
        ->get();

        return view('cotizaciones.cliente.index', compact('cotizaciones'));
    }


    public function aceptar($id)
    {
        $c = Cotizacion::with(['detalles'])->where('id_cotizacion',$id)
            ->where('id_cliente', \Auth::id())->firstOrFail();

        if ($c->estado !== 'Respondida' || is_null($c->precio_final)) {
            return back()->withErrors('Solo puedes aceptar cotizaciones respondidas con precio final.');
        }

        \DB::transaction(function() use ($c) {
            // Evitar duplicados si ya existe pedido
            $pedidoExistente = Pedido::where('id_cotizacion', $c->id_cotizacion)->first();
            if (!$pedidoExistente) {
                $p = Pedido::create([
                    'id_cotizacion' => $c->id_cotizacion,
                    'id_cliente'    => $c->id_cliente,
                    'id_agente'     => $c->id_usuario, // quien respondió la cotización, si existe - VJ
                    'total'         => $c->precio_final, // se conserva el precio aceptado - VJ
                    'estado'        => 'En proceso',
                    // 'fecha' se setea por defecto a CURRENT_DATE - VJ
                ]);

                foreach ($c->detalles as $d) {
                    PedidoDetalle::create([
                        'id_pedido'      => $p->id_pedido,
                        'id_producto'    => $d->id_producto,
                        'id_material'    => $d->id_material,
                        'cantidad'       => $d->cantidad,
                        'precio_unitario'=> $d->precio_unitario, // precio original cotizado (base+recargo)
                        'subtotal'       => $d->subtotal,
                    ]);
                }
            }

            $c->estado = 'Aprobada';
            if ($c->id_usuario && $c->agente?->correo) {
                Mail::to($c->agente->correo)->send(new CotizacionAprobadaMail($c));
            }
            $c->save();
        });

        return back()->with('ok','Has aprobado la cotización. Se creó el pedido.');
}


    public function rechazar($id)
    {
        $c = Cotizacion::where('id_cotizacion',$id)->where('id_cliente',Auth::id())->firstOrFail();
        if (!in_array($c->estado, ['Pendiente','Respondida'])) {
            return back()->withErrors('No puedes rechazar en el estado actual.');
        }
        $c->estado = 'Rechazada';
        if ($c->id_usuario && $c->agente?->correo) {
            Mail::to($c->agente->correo)->send(new CotizacionRechazadaMail($c));
        }
        $c->save();
        return back()->with('ok','Has rechazado la cotización.');
    }
}