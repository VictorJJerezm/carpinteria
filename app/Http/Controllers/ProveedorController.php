<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudCotizacionProveedorMail;
use App\Models\ProveedorMensaje;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string)$request->query('q', ''));
        $tag  = trim((string)$request->query('tag',''));
        $act  = $request->query('activo', 'todos'); // todos|1|0
        $perPage = 8;

        $prov = Proveedor::query()
            ->when($q !== '', function($qq) use ($q) {
                $qq->where(function($w) use ($q) {
                    $w->where('nombre','ilike',"%{$q}%")
                      ->orWhere('empresa','ilike',"%{$q}%")
                      ->orWhere('correo','ilike',"%{$q}%")
                      ->orWhere('telefono','ilike',"%{$q}%");
                });
            })
            ->when($tag !== '', fn($qq) => $qq->where('etiquetas','ilike',"%{$tag}%"))
            ->when(in_array($act, ['0','1'], true), fn($qq) => $qq->where('activo', (bool)$act))
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        // Nube de etiquetas rápida
        $todasEtiquetas = Proveedor::query()
            ->select('etiquetas')
            ->whereNotNull('etiquetas')
            ->pluck('etiquetas')
            ->flatMap(fn($e) => preg_split('/\s*,\s*/', $e, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($e) => trim($e))
            ->filter()
            ->countBy()           // etiqueta => conteo
            ->sortKeys()
            ->toArray();

        return view('proveedores.index', compact('prov','q','tag','act','todasEtiquetas'));
    }

    public function create()
    {
        $proveedor = new Proveedor();
        return view('proveedores.create', compact('proveedor'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => ['required','string','max:160'],
            'correo'   => ['required','email','max:160'],
            'telefono' => ['nullable','string','max:40'],
            'empresa'  => ['nullable','string','max:160'],
            'etiquetas'=> ['nullable','string','max:500'],
            'notas'    => ['nullable','string','max:2000'],
            'activo'   => ['nullable','boolean'],
        ]);
        $data['activo'] = (bool)($data['activo'] ?? true);

        Proveedor::create($data);
        return redirect()->route('proveedores.index')->with('ok','Proveedor creado.');
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'nombre'   => ['required','string','max:160'],
            'correo'   => ['required','email','max:160'],
            'telefono' => ['nullable','string','max:40'],
            'empresa'  => ['nullable','string','max:160'],
            'etiquetas'=> ['nullable','string','max:500'],
            'notas'    => ['nullable','string','max:2000'],
            'activo'   => ['nullable','boolean'],
        ]);
        $data['activo'] = (bool)($data['activo'] ?? $proveedor->activo);

        $proveedor->update($data);
        return redirect()->route('proveedores.index')->with('ok','Cambios guardados.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('ok','Proveedor eliminado.');
    }

    public function toggle(Proveedor $proveedor)
    {
        $proveedor->activo = ! $proveedor->activo;
        $proveedor->save();
        return back()->with('ok', $proveedor->activo ? 'Proveedor activado.' : 'Proveedor desactivado.');
    }

    // --- Solicitud de cotización por correo ---

    public function formSolicitud(Proveedor $proveedor)
    {
        // Plantilla base del mensaje
        $plantilla = "Estimado/a {$proveedor->nombre}:\n\n".
                     "Solicitamos muy amablemente una cotización de los siguientes productos/insumos:\n\n".
                     "- (Ejemplo) Cedro 2x1, 5 planchas\n".
                     "- (Ejemplo) Tornillos tirafondos 2\", 3 cajas\n\n".
                     "Por favor incluir: precio unitario, tiempo de entrega, condiciones de pago y validez de la oferta.\n\n".
                     "Quedamos atentos.\n\n".
                     "Saludos,\nCarpintería";
        return view('proveedores.solicitar', compact('proveedor','plantilla'));
    }

    public function enviarSolicitud(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'asunto'  => ['required','string','max:200'],
            'mensaje' => ['required','string','max:8000'],
            'cc'      => ['nullable','email'],
        ]);

        $dest = $proveedor->correo;
        $mailable = new SolicitudCotizacionProveedorMail($proveedor, $data['asunto'], $data['mensaje']);

        // CC opcional
        if (!empty($data['cc'])) {
            Mail::to($dest)->cc($data['cc'])->send($mailable);
        } else {
            Mail::to($dest)->send($mailable);

            ProveedorMensaje::create([
                'id_proveedor' => $proveedor->id_proveedor,
                'direccion'    => 'saliente',
                'asunto'       => $data['asunto'],
                'cuerpo'       => $data['mensaje'], // puedes guardar HTML si tu correo lo usa
                'de_email'     => config('mail.from.address'),
                'para_email'   => $proveedor->correo,
                'cc'           => $data['cc'] ?? null,
                'fecha'        => now(),
                'message_id'   => null, // lo tendremos cuando sincronicemos "Sent" (opcional)
                'adjuntos'     => null,
            ]);
        }

        return redirect()->route('proveedores.index')->with('ok','Solicitud enviada por correo.');
    }
}
