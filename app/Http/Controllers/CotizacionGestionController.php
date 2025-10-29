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
        $estado = $request->query('estado'); // Pendiente | Respondida | Aprobada | Rechazada | Cancelada
        $q = Cotizacion::with([
            'cliente:id,nombre,correo',
            'detalles.producto:id_producto,nombre,precio_estimado,foto_path',
            'detalles.material:id_insumo,nombre',
        ])->orderBy('id_cotizacion','desc');

        if ($estado) $q->where('estado', $estado);

        $cotizaciones = $q->get();
        return view('cotizaciones.admin.index', compact('cotizaciones','estado'));
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