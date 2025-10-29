@extends('layouts.app')
@section('contenido')
<h1>Proveedores</h1>

<div class="card mt-2">
  <div class="card-body">
    <form method="GET" class="form-row">
      <div>
        <label>Búsqueda</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre, empresa, correo...">
      </div>
      <div>
        <label>Etiqueta</label>
        <input type="text" name="tag" value="{{ $tag }}" list="lista-etiquetas" placeholder="maderas, ferretería...">
        <datalist id="lista-etiquetas">
          @foreach($todasEtiquetas as $et => $cnt) <option value="{{ $et }}">{{ $et }} ({{ $cnt }})</option> @endforeach
        </datalist>
      </div>
      <div>
        <label>Estado</label>
        <select name="activo">
          <option value="todos" {{ $act==='todos'?'selected':'' }}>Todos</option>
          <option value="1"     {{ $act==='1'    ?'selected':'' }}>Activos</option>
          <option value="0"     {{ $act==='0'    ?'selected':'' }}>Inactivos</option>
        </select>
      </div>
      <div class="items-end flex">
        <button class="btn btn-secondary btn-sm">Aplicar</button>
      </div>
      <div class="spacer"></div>
      <a class="btn btn-primary btn-sm" href="{{ route('proveedores.create') }}">Nuevo</a>
    </form>
  </div>
</div>

<div class="card mt-2">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>Proveedor</th><th>Empresa</th><th>Correo</th><th>Teléfono</th><th>Etiquetas</th><th>Estado</th><th style="width:220px">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($prov as $p)
          <tr>
            <td>{{ $p->nombre }}</td>
            <td>{{ $p->empresa ?? '—' }}</td>
            <td class="muted">{{ $p->correo }}</td>
            <td class="muted">{{ $p->telefono ?? '—' }}</td>
            <td>
              @forelse($p->etiquetas_array as $e)
                <span class="badge">{{ $e }}</span>
              @empty
                <span class="muted">—</span>
              @endforelse
            </td>
            <td>
              <span class="badge {{ $p->activo ? 'badge-ok' : 'badge-bad' }}">{{ $p->activo ? 'Activo' : 'Inactivo' }}</span>
            </td>
            <td class="flex gap-1">
              <a class="btn btn-secondary btn-sm" href="{{ route('proveedores.edit',$p->id_proveedor) }}">Editar</a>
              <a class="btn btn-primary btn-sm"  href="{{ route('proveedores.solicitud',$p->id_proveedor) }}">Solicitar</a>
              <form method="POST" action="{{ route('proveedores.toggle',$p->id_proveedor) }}" onsubmit="return confirm('¿Cambiar estado?')">
                @csrf @method('PATCH')
                <button class="btn btn-sm">{{ $p->activo ? 'Desactivar' : 'Activar' }}</button>
              </form>
              <form method="POST" action="{{ route('proveedores.destroy',$p->id_proveedor) }}" onsubmit="return confirm('¿Eliminar proveedor?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7">Sin proveedores.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt-2">{{ $prov->links() }}</div>
  </div>
</div>
@endsection