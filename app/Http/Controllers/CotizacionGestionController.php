<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cotizacion;
use Illuminate\Support\Facades\Mail;
use App\Mail\CotizacionRespondidaMail;

class CotizacionGestionController extends Controller
{
    // Listado con filtro por estado
    public function index(Request $request)
    {
        $q        = trim($request->query('q', ''));
        $estado   = $request->query('estado', '');
        $desde    = $request->query('desde', '');
        $hasta    = $request->query('hasta', '');
        $cliente  = (int) $request->query('cliente', 0); 
        $perPage  = (int) $request->query('pp', 8);
        if ($perPage < 1 || $perPage > 50) $perPage = 8;

        $cotizaciones = \App\Models\Cotizacion::query()
            ->with(['usuarioCliente'])
            ->when($q !== '', fn($qq) => $qq->where(function($w) use ($q){
                $w->where('comentario', 'ilike', "%{$q}%")
                ->orWhere('id_cotizacion', $q);
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

        $clientes = \DB::table('cotizaciones as c')
            ->join('usuarios as u', 'u.id', '=', 'c.id_cliente')
            ->distinct()
            ->orderBy('u.nombre')
            ->get(['u.id', 'u.nombre', 'u.correo']);

        return view('cotizaciones.admin.index', compact(
            'cotizaciones','clientes','q','estado','desde','hasta','cliente','perPage'
        ));
    }

    // Ver detalle
    public function show($id)
    {
        $c = Cotizacion::with([
            'cliente:id,nombre,correo',
            'detalles.producto:id_producto,nombre,precio_estimado,foto_path',
            'detalles.material:id_insumo,nombre',
        ])->findOrFail($id);

        return view('cotizaciones.admin.show', compact('c'));
    }

    // Formulario para responder
    public function formResponder($id)
    {
        $c = Cotizacion::with([
            'cliente:id,nombre,correo',
            'detalles.producto:id_producto,nombre,precio_estimado,foto_path',
            'detalles.material:id_insumo,nombre',
        ])->findOrFail($id);

        // Solo permitir responder si está pendiente o quieres re-responder
        return view('cotizaciones.admin.responder', compact('c'));
    }

    // Guardar respuesta
    public function responder(Request $request, $id)
    {
        $data = $request->validate([
            'precio_final'        => ['required','numeric','min:0'],
            'tiempo_estimado_dias'=> ['nullable','integer','min:0','max:3650'],
            'respuesta'           => ['nullable','string','max:5000'],
        ]);

        $c = Cotizacion::findOrFail($id);
        $c->precio_final         = $data['precio_final'];
        $c->tiempo_estimado_dias = $data['tiempo_estimado_dias'] ?? null;
        $c->respuesta            = $data['respuesta'] ?? null;
        $c->fecha_respuesta      = date('Y-m-d');
        $c->id_usuario           = Auth::id();
        $c->estado               = 'Respondida';
        $c->save();

        Mail::to($c->cliente?->correo)->send(new CotizacionRespondidaMail($c));

        return redirect()->route('gestion.cotizaciones.show', $c->id_cotizacion)
            ->with('ok','Cotización respondida.');
    }

    // Cancelar la cotización (por admin/carpintero)
    public function cancelar($id)
    {
        $c = Cotizacion::findOrFail($id);
        if (in_array($c->estado, ['Aprobada','Rechazada','Cancelada'])) {
            return back()->withErrors('No se puede cancelar en el estado actual.');
        }
        $c->estado = 'Cancelada';
        $c->save();
        return back()->with('ok','Cotización cancelada.');
    }
}