@extends('layouts.app')
@section('contenido')
<h1>Cotizaciones</h1>

<div class="form-actions">
  <a href="{{ route('gestion.cotizaciones.index') }}" class="btn btn-secondary btn-sm">Todas</a>
  @foreach(['Pendiente','Respondida','Aprobada','Rechazada','Cancelada'] as $st)
    <a href="{{ route('gestion.cotizaciones.index', ['estado'=>$st]) }}" class="btn btn-secondary btn-sm">{{ $st }}</a>
  @endforeach
</div>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Cliente</th><th>Fecha</th><th>Detalle</th><th>Estado</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($cotizaciones as $c)
          <tr>
            <td>{{ $c->id_cotizacion }}</td>
            <td>{{ $c->cliente?->nombre ?? '—' }}</td>
            <td>{{ $c->fecha }}</td>
            <td>
            @foreach($c->detalles as $d)
            <div class="flex items-center gap-2">
                @if($d->producto?->foto_url)
                <img src="{{ $d->producto->foto_url }}" alt="Foto {{ $d->producto?->nombre }}" class="thumb-xs">
                @endif
                <div>
                {{ $d->producto?->nombre ?? 'Producto' }}
                @if($d->material) — <small class="muted">{{ $d->material->nombre }}</small>@endif
                × {{ $d->cantidad }}
                </div>
            </div>
            @endforeach
            </td>
            <td>
              @php
                $badge = match($c->estado){
                  'Pendiente' => 'badge-warn',
                  'Respondida'=> 'badge-neutral',
                  'Aprobada'  => 'badge-ok',
                  'Rechazada' => 'badge-bad',
                  'Cancelada' => 'badge-bad',
                  default     => 'badge-neutral'
                };
              @endphp
              <span class="badge {{ $badge }}">{{ $c->estado }}</span>
            </td>

            <td class="actions">
              <a class="btn btn-secondary btn-sm" href="{{ route('gestion.cotizaciones.show',$c->id_cotizacion) }}">Ver</a>
              @if($c->estado==='Pendiente')
                @if($c->requiere_confirmacion_medidas)
                  <div><small class="muted">Medidas: </small><span class="badge badge-warn">Requiere confirmación</span></div>
                @endif
                <a class="btn btn-primary btn-sm" href="{{ route('gestion.cotizaciones.form',$c->id_cotizacion) }}">Responder</a>
                <form method="POST" action="{{ route('gestion.cotizaciones.cancelar',$c->id_cotizacion) }}" onsubmit="return confirm('¿Cancelar cotización?')">
                  @csrf
                  <button class="btn btn-danger btn-sm" type="submit">Cancelar</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6">Sin cotizaciones</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
