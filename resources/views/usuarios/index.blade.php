@extends('layouts.app')
@section('contenido')
<h1>Usuarios</h1>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-2">
  <div class="card-body">
    <form method="GET" class="form-row">
      <div>
        <label>Buscar</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o correo">
      </div>
      <div>
        <label>Rol</label>
        <select name="rol">
          <option value="">— Todos —</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" {{ (string)$rol===(string)$r->id?'selected':'' }}>{{ $r->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>Estado</label>
        <select name="estado">
          <option value="">— Todos —</option>
          <option value="Activo"   {{ $estado==='Activo'?'selected':'' }}>Activo</option>
          <option value="Inactivo" {{ $estado==='Inactivo'?'selected':'' }}>Inactivo</option>
        </select>
      </div>
      <div class="items-end flex">
        <button class="btn btn-secondary btn-sm" type="submit">Filtrar</button>
      </div>
      <div class="items-end flex" style="margin-left:auto">
        <a class="btn btn-primary btn-sm" href="{{ route('usuarios.create') }}">Nuevo</a>
      </div>
    </form>
  </div>
</div>

<div class="card mt-2">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-stacked">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $u)
            @php
              $badge = $u->activo ? 'badge-ok' : 'badge-bad';
              $estadoTxt = $u->activo ? 'Activo' : 'Inactivo';
            @endphp
            <tr>
              <td data-label="#">{{ $u->id }}</td>
              <td data-label="Nombre">{{ $u->nombre }}</td>
              <td data-label="Correo" class="muted">{{ $u->correo }}</td>
              <td data-label="Rol">{{ $u->rol?->nombre ?? '—' }}</td>
              <td data-label="Estado"><span class="badge {{ $badge }}">{{ $estadoTxt }}</span></td>
              <td data-label="Acciones" class="actions">
                <a class="btn btn-secondary btn-sm" href="{{ route('usuarios.edit', $u->id) }}">Editar</a>

                @if($u->activo)
                  <form method="POST" action="{{ route('usuarios.desactivar', $u->id) }}" style="display:inline" onsubmit="return confirm('¿Desactivar usuario?')">
                    @csrf
                    <button class="btn btn-warning btn-sm" type="submit">Desactivar</button>
                  </form>
                @else
                  <form method="POST" action="{{ route('usuarios.activar', $u->id) }}" style="display:inline" onsubmit="return confirm('¿Activar usuario?')">
                    @csrf
                    <button class="btn btn-primary btn-sm" type="submit">Activar</button>
                  </form>
                @endif

                <form method="POST" action="{{ route('usuarios.destroy', $u->id) }}" style="display:inline" onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6">Sin usuarios.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-2">
      {{-- Info tipo: “Mostrando 1–8 de 24 usuarios” --}}
      @if($users->total() > 0)
        <p class="muted" style="margin-bottom:.5rem">
          Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
        </p>
      @endif

      {{-- Links de paginación --}}
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection
