@extends('layouts.app')
@section('contenido')
<h1>Inventario</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-2">
  <div class="card-body">
    <form method="GET" class="form-row">
      <div><label>Buscar</label><input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Nombre o descripción"></div>
      <div>
        <label>Categoría</label>
        <select name="categoria">
          <option value="">— Todas —</option>
          @foreach($categorias as $c)
            <option value="{{ $c }}" {{ ($categoria ?? '')===$c ? 'selected' : '' }}>
              {{ ucfirst($c) }}
            </option>
          @endforeach
        </select>
      </div>
      <div><label>Estado</label>
        <select name="estado">
          <option value="">— Todos —</option>
          <option value="Activo"   {{ ($estado ?? '')==='Activo'?'selected':'' }}>Activo</option>
          <option value="Inactivo" {{ ($estado ?? '')==='Inactivo'?'selected':'' }}>Inactivo</option>
        </select>
      </div>
      <div><label>Stock</label>
        <select name="stock">
          <option value="">— Todos —</option>
          <option value="bajo"   {{ ($stock ?? '')==='bajo'?'selected':'' }}>Bajo (&lt; 5)</option>
          <option value="normal" {{ ($stock ?? '')==='normal'?'selected':'' }}>Normal (≥ 5)</option>
        </select>
      </div>
      <div><label>Desde</label><input type="date" name="desde" value="{{ $desde ?? '' }}"></div>
      <div><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta ?? '' }}"></div>
      <div><label>Por página</label>
        <select name="pp">
          @foreach([8,12,16,24] as $x)
            <option value="{{ $x }}" {{ (int)($pp ?? 8)===$x?'selected':'' }}>{{ $x }}</option>
          @endforeach
        </select>
      </div>
      <div class="items-end flex">
        <button class="btn btn-secondary btn-sm" type="submit">Filtrar</button>
      </div>
      <a class="btn btn-sm" href="{{ route('inventario.index') }}">Limpiar filtros</a>
    </form>
  </div>  
</div>

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
    @if($insumos->total() > 0)
      <p class="muted mt-2" style="margin-bottom:.5rem">
        Mostrando {{ $insumos->firstItem() }}–{{ $insumos->lastItem() }} de {{ $insumos->total() }} registros
      </p>
    @endif
    <div class="pagination-compact">
      {{ $insumos->onEachSide(1)->links() }}
    </div>
  </div>
</div>

{{-- ================= Movimientos recientes ================= --}}
<div class="card mt-3">
  <div class="card-body">

    <div class="form-row" style="align-items:flex-end">
      <div>
        <form method="GET" action="{{ route('inventario.index') }}" class="form-row" style="align-items:flex-end">
          <div>
            <label>Filtrar por insumo</label>
            <select name="insumo" onchange="this.form.submit()">
              <option value="0">— Todos —</option>
              @foreach($insumosFiltro as $inf)
                <option value="{{ $inf->id_insumo }}" {{ (int)$insumoSel===(int)$inf->id_insumo ? 'selected':'' }}>
                  {{ $inf->nombre }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- conservar filtros de inventario al filtrar movimientos --}}
          <input type="hidden" name="q"         value="{{ $q }}">
          <input type="hidden" name="categoria" value="{{ $categoria }}">
          <input type="hidden" name="estado"    value="{{ $estado }}">
          <input type="hidden" name="stock"     value="{{ $stock }}">
          <input type="hidden" name="desde"     value="{{ $desde }}">
          <input type="hidden" name="hasta"     value="{{ $hasta }}">
          <input type="hidden" name="pp"        value="{{ $pp }}">
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
    @if($movimientos->total() > 0)
      <p class="muted mt-2" style="margin-bottom:.5rem">
        Mostrando {{ $movimientos->firstItem() }}–{{ $movimientos->lastItem() }} de {{ $movimientos->total() }} movimientos
      </p>
    @endif
    <div class="pagination-compact">
      {{ $movimientos->onEachSide(1)->withPath(request()->url().'#movs')->links() }}
    </div>
  </div>
</div>
@endsection


