@extends('layouts.app')
@section('contenido')
<h1>Mis cotizaciones</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <div class="table-responsive">

      <table class="table cots">
        <thead>
          <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Detalle</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($cotizaciones as $c)
          @php
            $badgeCot = match($c->estado){
              'Pendiente' => 'badge-warn',
              'Respondida'=> 'badge-neutral',
              'Aprobada'  => 'badge-ok',
              'Rechazada','Cancelada' => 'badge-bad',
              default     => 'badge-neutral'
            };
            $badgePed = match($c->pedido?->estado){
              'En proceso' => 'badge-warn',
              'Terminado'  => 'badge-neutral',
              'Entregado'  => 'badge-ok',
              default      => 'badge-neutral'
            };
          @endphp

          <tr>
            <td data-label="#">{{ $c->id_cotizacion }}</td>

            <td data-label="Fecha">{{ $c->fecha }}</td>

            <td data-label="Detalle">
              @forelse($c->detalles as $d)
                <div>
                  {{ $d->producto?->nombre }}
                  @if($d->material) — <small class="muted">{{ $d->material->nombre }}</small>@endif
                  × {{ $d->cantidad }}
                </div>
              @empty
                <span class="muted">Sin detalle</span>
              @endforelse
            </td>

            <td data-label="Estado">
              <div><span class="badge {{ $badgeCot }}">{{ $c->estado }}</span></div>
              @if($c->pedido)
                <div class="mt-1">
                  <small class="muted">Pedido:</small>
                  <span class="badge {{ $badgePed }}">{{ $c->pedido->estado }}</span>
                </div>
              @endif
            </td>

            <td data-label="Total">
              @if(!is_null($c->precio_final))
                <strong>Q {{ number_format($c->precio_final,2) }}</strong>
                @if(!is_null($c->tiempo_estimado_dias))
                  <div class="muted">{{ $c->tiempo_estimado_dias }} días</div>
                @endif
              @else
                <span class="muted">A definir</span>
              @endif
            </td>

            <td data-label="Acciones" class="actions">
              @if($c->estado === 'Respondida')
                <form method="POST" action="{{ route('cliente.cotizaciones.aceptar',$c->id_cotizacion) }}" style="display:inline" onsubmit="return confirm('¿Aceptar respuesta?')">
                  @csrf
                  <button class="btn btn-primary btn-sm" type="submit">Aceptar</button>
                </form>
                <form method="POST" action="{{ route('cliente.cotizaciones.rechazar',$c->id_cotizacion) }}" style="display:inline" onsubmit="return confirm('¿Rechazar cotización?')">
                  @csrf
                  <button class="btn btn-secondary btn-sm" type="submit">Rechazar</button>
                </form>
              @else
                <span class="muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr class="no-data"><td colspan="6">Aún no tienes cotizaciones.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
