@extends('layouts.app')
@section('contenido')
<h1>Responder cotización #{{ $c->id_cotizacion }}</h1>

@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

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
    <form method="POST" action="{{ route('gestion.cotizaciones.responder', $c->id_cotizacion) }}">
      @csrf

      <label>Precio final (Q)</label>
      <input type="number" step="0.01" name="precio_final" required value="{{ old('precio_final', $c->precio_final ?? $c->costo_total) }}">

      <label>Tiempo estimado (días)</label>
      <input type="number" name="tiempo_estimado_dias" min="0" value="{{ old('tiempo_estimado_dias', $c->tiempo_estimado_dias) }}">

      <label>Mensaje al cliente (opcional)</label>
      <textarea name="respuesta" rows="4">{{ old('respuesta', $c->respuesta) }}</textarea>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Enviar respuesta</button>
        <a class="btn btn-secondary" href="{{ route('gestion.cotizaciones.show',$c->id_cotizacion) }}">Cancelar</a>
      </div>
    </form>
  </div></div>
</div>
@endsection
