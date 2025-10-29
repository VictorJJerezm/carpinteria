@extends('layouts.app')
@section('contenido')
<h1>Insumos</h1>
@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif

<div class="form-actions">
  <a href="{{ route('insumos.create') }}" class="btn btn-primary">+ Nuevo insumo</a>
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
  </div>
</div>
@endsection
