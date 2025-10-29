@extends('layouts.app')
@section('contenido')
<h1>Productos</h1>
@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif

<div class="form-actions">
  <a href="{{ route('productos.create') }}" class="btn btn-primary">+ Nuevo</a>
</div>

<div class="mt-3 card">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Foto</th>
          <th>Nombre</th>
          <th>Precio</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($productos as $p)
          <tr>
            <td>{{ $p->id_producto }}</td>
            <td style="width:120px">
              @if($p->foto_url)
                <img src="{{ $p->foto_url }}" alt="Foto {{ $p->nombre }}" style="width:120px; height:80px; object-fit:cover;">
              @else — @endif
            </td>
            <td>{{ $p->nombre }}</td>
            <td>Q {{ number_format($p->precio_estimado,2) }}</td>
            <td>
              @if($p->estado==='Activo')
                <span class="badge badge-ok">Activo</span>
              @else
                <span class="badge badge-warn">Inactivo</span>
              @endif
            </td>
            <td class="actions">
              <a class="btn btn-secondary btn-sm" href="{{ route('productos.edit', $p->id_producto) }}">Editar</a>
              <form action="{{ route('productos.destroy', $p->id_producto) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6">Sin productos</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
