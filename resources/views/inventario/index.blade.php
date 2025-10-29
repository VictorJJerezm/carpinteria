@extends('layouts.app')
@section('contenido')
<h1>Inventario</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>Insumo</th><th>Stock</th><th>Mínimo</th><th>Actualizado</th><th>Entrada</th><th>Salida</th>
        </tr>
      </thead>
      <tbody>
        @forelse($insumos as $i)
          @php
            $inv = $i->inventario;
            $stock = $inv?->cantidad ?? 0;
            $min = $inv?->stock_minimo ?? 5;
          @endphp
          <tr>
            <td>{{ $i->nombre }}</td>
            <td>
              {{ $stock }}
              @if($stock < $min) <span class="badge badge-bad">Bajo</span> @endif
            </td>
            <td>{{ $min }}</td>
            <td>{{ $inv?->fecha_actualizacion ?? '—' }}</td>
            <td>
              <form method="POST" action="{{ route('inventario.entrada', $i->id_insumo) }}" class="flex gap-2">
                @csrf
                <input type="number" name="cantidad" min="1" required placeholder="cant.">
                <input type="number" step="0.01" name="costo_unitario" required placeholder="costo">
                <input type="text" name="nota" placeholder="nota (opcional)">
                <button type="submit" class="btn btn-primary btn-sm">+ Entrar</button>
              </form>
            </td>
            <td>
              <form method="POST" action="{{ route('inventario.salida', $i->id_insumo) }}" class="flex gap-2">
                @csrf
                <input type="number" name="cantidad" min="1" required placeholder="cant.">
                <input type="text" name="nota" placeholder="nota (opcional)">
                <button type="submit" class="btn btn-secondary btn-sm">− Salida</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6">No hay insumos</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ================= Movimientos recientes ================= --}}
<div class="card mt-3">
  <div class="card-body">

    <div class="form-row" style="align-items:flex-end">
      <div>
        <label>Filtrar por insumo</label>
        <form method="GET" action="{{ route('inventario.index') }}" class="form-row">
          <select name="insumo" onchange="this.form.submit()">
            <option value="0">— Todos —</option>
            @foreach($insumosFiltro as $inf)
              <option value="{{ $inf->id_insumo }}" {{ (int)$insumoSel===(int)$inf->id_insumo ? 'selected':'' }}>
                {{ $inf->nombre }}
              </option>
            @endforeach
          </select>
          <label style="margin-left:1rem">Mostrar</label>
          <select name="limite" onchange="this.form.submit()">
            @foreach([5,10,25,50,100] as $n)
              <option value="{{ $n }}" {{ (int)$limite===$n ? 'selected':'' }}>{{ $n }}</option>
            @endforeach
          </select>
        </form>
      </div>

      <div class="spacer"></div>
      {{-- (Opcional) botón para exportar CSV de movimientos filtrados --}}
      {{-- <a class="btn btn-secondary btn-sm" href="{{ route('inventario.movs.export', ['insumo'=>$insumoSel]) }}">Exportar movimientos</a> --}}
    </div>

    <h3 class="mt-2">Movimientos recientes</h3>

    <table class="table mt-1">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Insumo</th>
          <th>Tipo</th>
          <th class="text-right">Cantidad</th>
          <th class="text-right">Costo unit.</th>
          <th class="text-right">Importe</th>
          <th>Nota</th>
          <th>Usuario</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movimientos as $m)
          @php
            $isEntrada = strtolower($m->tipo) === 'entrada';
            $badge = $isEntrada ? 'badge-ok' : 'badge-bad';
            $cant  = ($isEntrada ? '+' : '−') . (int)$m->cantidad;
            $costo = $m->costo_unitario !== null ? number_format($m->costo_unitario,2) : '—';
            $importe = $isEntrada && $m->costo_unitario !== null
                      ? $m->cantidad * $m->costo_unitario
                      : 0;
          @endphp
          <tr>
            <td>{{ \Illuminate\Support\Str::of($m->fecha)->limit(16,'') }}</td>
            <td>{{ $m->insumo }}</td>
            <td><span class="badge {{ $badge }}">{{ ucfirst($m->tipo) }}</span></td>
            <td class="text-right">{{ $cant }}</td>
            <td class="text-right">{{ $costo === '—' ? '—' : 'Q '.$costo }}</td>
            <td class="text-right">{{ $importe ? 'Q '.number_format($importe,2) : '—' }}</td>
            <td class="muted">{{ $m->nota }}</td>
            <td class="muted">{{ $m->usuario ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="8">Sin movimientos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection


