@extends('layouts.app')
@section('contenido')
<h1>Pedido #{{ $p->id_pedido }}</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="grid grid-2 mt-3">
  <div class="card"><div class="card-body">
    <h3>Cliente</h3>
    <div><strong>{{ $p->cliente?->nombre }}</strong></div>
    <div class="muted">{{ $p->cliente?->correo }}</div>
    <div class="mt-2"><strong>Total:</strong> Q {{ number_format($p->total,2) }}</div>

    <h3 class="mt-3">Detalle</h3>
    @foreach($p->detalles as $d)
      <div class="flex items-center gap-2 mt-2">
        @if($d->producto?->foto_url)
          <img src="{{ $d->producto->foto_url }}" alt="Foto {{ $d->producto?->nombre }}" class="thumb-xs">
        @endif
        <div>
          <strong>{{ $d->producto?->nombre }}</strong>
          @if($d->material) — <small class="muted">{{ $d->material->nombre }}</small>@endif
          × {{ $d->cantidad }}
          <div class="muted">P. unitario: Q {{ number_format($d->precio_unitario,2) }}</div>
        </div>
      </div>
    @endforeach
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Estado</h3>
    <p><span class="badge badge-neutral">{{ $p->estado }}</span></p>

    <form method="POST" action="{{ route('pedidos.estado', $p->id_pedido) }}" class="form-row">
      @csrf
      <div>
        <label>Cambiar estado</label>
        <select name="estado" required>
          @foreach(['En proceso','Terminado','Entregado'] as $st)
            <option value="{{ $st }}" {{ $p->estado===$st?'selected':'' }}>{{ $st }}</option>
          @endforeach
        </select>
      </div>
      <div class="items-end flex">
        <button class="btn btn-primary" type="submit">Actualizar</button>
      </div>
    </form>

    <div class="form-actions mt-2">
      <a class="btn btn-secondary" href="{{ route('pedidos.index') }}">Volver</a>
    </div>
  </div></div>
</div>
@endsection
