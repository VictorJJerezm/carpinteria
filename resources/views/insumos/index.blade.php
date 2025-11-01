@extends('layouts.app')
@section('contenido')
<h1>Insumos</h1>
@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif

<div class="form-actions">
  <a href="{{ route('insumos.create') }}" class="btn btn-primary">+ Nuevo insumo</a>
</div>

<div class="card mt-2">
  <div class="card-body">
    <form method="GET" class="form-row">

      <div>
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Nombre o descripción">
      </div>

      <div>
        <label>Categoría</label>
        <select name="categoria">
          <option value="">— Todas —</option>
          @foreach($categorias as $c)
            <option value="{{ $c }}" {{ ($categoria ?? '')===$c?'selected':'' }}>
              {{ ucfirst($c) }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Tipo de material</label>
        <input type="text" name="tipo" value="{{ $tipo ?? '' }}" placeholder="Ej: madera, metal...">
      </div>

      <div>
        <label>Estado</label>
        <select name="estado">
          <option value="">— Todos —</option>
          <option value="Activo" {{ ($estado ?? '')==='Activo'?'selected':'' }}>Activo</option>
          <option value="Inactivo" {{ ($estado ?? '')==='Inactivo'?'selected':'' }}>Inactivo</option>
        </select>
      </div>

      <div>
        <label>Precio mín</label>
        <input type="number" step="0.01" name="min" value="{{ $min ?? '' }}" placeholder="0.00">
      </div>

      <div>
        <label>Precio máx</label>
        <input type="number" step="0.01" name="max" value="{{ $max ?? '' }}" placeholder="0.00">
      </div>

      <div>
        <label>Por página</label>
        <select name="pp">
          @foreach([8,12,16,24] as $pp)
            <option value="{{ $pp }}" {{ (int)($perPage ?? 8)===$pp?'selected':'' }}>{{ $pp }}</option>
          @endforeach
        </select>
      </div>

      <div class="items-end flex">
        <button class="btn btn-secondary btn-sm" type="submit">Filtrar</button>
      </div>

      <div class="items-end flex" style="margin-left:auto">
        <a href="{{ route('insumos.create') }}" class="btn btn-primary btn-sm">+ Nuevo</a>
      </div>

    </form>
  </div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th><th>Nombre</th><th>Categoría</th><th>Unidad</th><th>Dimensiones</th><th>Precio ref.</th><th>Stock</th><th>Estado</th><th>Acciones</th>
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
            <td>{{ $i->id_insumo }}</td>
            <td>{{ $i->nombre }}</td>
            <td>{{ $i->categoria ?? 'material' }}</td>
            <td>{{ $i->unidad ?? 'pieza' }}</td>
            <td>{{ $i->largo ?? 0 }} x {{ $i->alto ?? 0 }} x {{ $i->ancho ?? 0 }}</td>
            <td>Q {{ number_format($i->precio,2) }}</td>
            <td>
              {{ $stock }}
              @if($stock < $min)
                <span class="badge badge-bad">Bajo (min {{ $min }})</span>
              @endif
            </td>
            <td>
              @if($i->estado==='Activo')
                <span class="badge badge-ok">Activo</span>
              @else
                <span class="badge badge-warn">Inactivo</span>
              @endif
            </td>
            <td class="actions">
              <a href="{{ route('insumos.edit', $i->id_insumo) }}" class="btn btn-secondary btn-sm">Editar</a>
              <form method="POST" action="{{ route('insumos.destroy', $i->id_insumo) }}" onsubmit="return confirm('¿Eliminar insumo?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9">Sin insumos</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="mt-2">
      @if($insumos->total() > 0)
        <p class="muted" style="margin-bottom:.5rem">
          Mostrando {{ $insumos->firstItem() }}–{{ $insumos->lastItem() }} de {{ $insumos->total() }} insumos
        </p>
      @endif

      {{ $insumos->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection
