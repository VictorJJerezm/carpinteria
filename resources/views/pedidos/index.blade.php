@extends('layouts.app')
@section('contenido')
<h1>Pedidos</h1>

<form method="GET" action="{{ route('pedidos.index') }}" class="form-row">
  <div>
    <label>Cliente (usuario)</label>
    <select name="cliente" onchange="this.form.submit()">
      <option value="">— Todos —</option>
      @foreach($clientes as $u)
        <option value="{{ $u->id }}" {{ (string)($cliente ?? '')===(string)$u->id ? 'selected' : '' }}>
          {{ $u->nombre }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label>Por página</label>
    <select name="pp" onchange="this.form.submit()">
      @foreach([8,12,16,24] as $pp)
        <option value="{{ $pp }}" {{ (int)($perPage ?? 8)===$pp ? 'selected' : '' }}>{{ $pp }}</option>
      @endforeach
    </select>
  </div>

  <div class="form-actions" style="align-items:end; display:flex; gap:.5rem; flex-wrap:wrap;">
    <a href="{{ route('pedidos.index', array_filter([
          'cliente'=>$cliente, 'pp'=>$perPage,
          'q'=>$q, 'desde'=>$desde, 'hasta'=>$hasta,
    ])) }}" class="btn btn-secondary btn-sm">Todos</a>

    @foreach(['En proceso','Terminado','Entregado'] as $st) {{-- ajusta tus estados reales --}}
      <a href="{{ route('pedidos.index', array_filter([
            'estado'=>$st,'cliente'=>$cliente,'pp'=>$perPage,
            'q'=>$q, 'desde'=>$desde, 'hasta'=>$hasta,
      ])) }}"
         class="btn btn-secondary btn-sm {{ ($estado??'')===$st ? 'active' : '' }}">
        {{ $st }}
      </a>
    @endforeach
  </div>
</form>


@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Cliente</th><th>Estado</th><th>Total</th><th>Detalle</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pedidos as $p)
          <tr>
            <td>{{ $p->id_pedido }}</td>
            <td>{{ $p->cliente?->nombre }}</td>
            <td>
              @php
                $badge = match($p->estado){
                  'En proceso' => 'badge-warn',
                  'Terminado'  => 'badge-neutral',
                  'Entregado'  => 'badge-ok',
                  default      => 'badge-neutral'
                };
              @endphp
              <span class="badge {{ $badge }}">{{ $p->estado }}</span>
            </td>
            <td><strong>Q {{ number_format($p->total,2) }}</strong></td>
            <td>
              @foreach($p->detalles as $d)
                <div class="flex items-center gap-2">
                  @if($d->producto?->foto_url)
                    <img src="{{ $d->producto->foto_url }}" class="thumb-xs" alt="foto">
                  @endif
                  <div>{{ $d->producto?->nombre }} @if($d->material) — <small class="muted">{{ $d->material->nombre }}</small>@endif × {{ $d->cantidad }}</div>
                </div>
              @endforeach
            </td>
            <td class="actions">
              <a class="btn btn-secondary btn-sm" href="{{ route('pedidos.show',$p->id_pedido) }}">Ver</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6">No hay pedidos.</td></tr>
        @endforelse
      </tbody>
    </table>
    @if($pedidos->total() > 0)
      <p class="muted mt-2" style="margin-bottom:.5rem">
        Mostrando {{ $pedidos->firstItem() }}–{{ $pedidos->lastItem() }} de {{ $pedidos->total() }} pedidos
      </p>
    @endif

    <div class="pagination-compact">
      {{ $pedidos->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection
