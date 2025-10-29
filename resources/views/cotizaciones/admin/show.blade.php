@extends('layouts.app')
@section('contenido')
<h1>Cotización #{{ $c->id_cotizacion }}</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="grid grid-2 mt-3">
  <div class="card"><div class="card-body">
    <h3>Cliente</h3>
    <div><strong>{{ $c->cliente?->nombre }}</strong></div>
    <div class="muted">{{ $c->cliente?->correo }}</div>

    <h3 class="mt-3">Detalle</h3>
    @foreach($c->detalles as $d)
    <div class="flex items-center gap-2 mt-2">
        @if($d->producto?->foto_url)
        <img src="{{ $d->producto->foto_url }}" alt="Foto {{ $d->producto?->nombre }}" class="thumb-xs">
        @endif
        <div>
        <strong>{{ $d->producto?->nombre }}</strong>
        @if($d->material) — <small class="muted">{{ $d->material->nombre }}</small>@endif
        × {{ $d->cantidad }}
        <div class="muted">P. unitario base: Q {{ number_format($d->precio_unitario,2) }}</div>
        </div>
    </div>
    @endforeach

    <div class="mt-2"><strong>Total cliente:</strong> Q {{ number_format($c->costo_total,2) }}</div>

    @if($c->comentario)
      <div class="mt-2">
        <strong>Comentario del cliente:</strong>
        <div class="muted">{{ $c->comentario }}</div>
      </div>
    @endif

    <div class="mt-2">
      <strong>Confirmación de medidas:</strong>
      @if($c->requiere_confirmacion_medidas)
        <span class="badge badge-warn">Sí</span>
      @else
        <span class="badge badge-neutral">No</span>
      @endif
    </div>

  </div></div>

  <div class="card"><div class="card-body">
    <h3>Estado</h3>
    <p>
      <span class="badge badge-neutral">{{ $c->estado }}</span>
    </p>

    @if($c->estado === 'Respondida')
      <div><strong>Precio final:</strong> Q {{ number_format($c->precio_final,2) }}</div>
      @if(!is_null($c->tiempo_estimado_dias))
        <div><strong>Tiempo estimado:</strong> {{ $c->tiempo_estimado_dias }} días</div>
      @endif
      @if($c->respuesta)
        <div class="mt-1"><strong>Mensaje:</strong> <div class="muted">{{ $c->respuesta }}</div></div>
      @endif
    @endif

    <div class="form-actions mt-3">
      @if($c->estado==='Pendiente')
        <a class="btn btn-primary" href="{{ route('gestion.cotizaciones.form',$c->id_cotizacion) }}">Responder</a>
        <form method="POST" action="{{ route('gestion.cotizaciones.cancelar',$c->id_cotizacion) }}" onsubmit="return confirm('¿Cancelar cotización?')">
          @csrf
          <button class="btn btn-danger" type="submit">Cancelar</button>
        </form>
      @endif
      <a class="btn btn-secondary" href="{{ route('gestion.cotizaciones.index') }}">Volver</a>
    </div>
  </div></div>
</div>
@endsection
