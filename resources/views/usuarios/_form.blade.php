@php
  $v = fn($c,$d='') => old($c, isset($usuario)? $usuario->{$c} : $d);
@endphp

<label>Nombre</label>
<input type="text" name="nombre" value="{{ $v('nombre') }}" required>

<div class="form-row">
  <div>
    <label>Correo</label>
    <input type="email" name="correo" value="{{ $v('correo') }}" required>
  </div>
  <div>
    <label>Teléfono (opcional)</label>
    <input type="text" name="telefono" value="{{ $v('telefono') }}">
  </div>
</div>

<div class="form-row">
  <div>
    <label>Rol</label>
    <select name="id_rol" required>
      @foreach($roles as $r)
        <option value="{{ $r->id }}" {{ (int)$v('id_rol')===$r->id ? 'selected':'' }}>
          {{ $r->nombre }}
        </option>
      @endforeach
    </select>
  </div>
  <div>
    <label>Estado</label>
    @php $act = $v('activo', 1); @endphp
    <select name="activo" required>
      <option value="1" {{ (int)$act===1?'selected':'' }}>Activo</option>
      <option value="0" {{ (int)$act===0?'selected':'' }}>Inactivo</option>
    </select>
  </div>
</div>

<div class="form-row">
  <div>
    <label>Contraseña {{ isset($usuario)? '(dejar en blanco para mantener)': '' }}</label>
    <input type="password" name="contra" {{ isset($usuario)? '': 'required' }}>
  </div>
  <div>
    <label>Confirmar contraseña</label>
    <input type="password" name="contra_confirmation" {{ isset($usuario)? '': 'required' }}>
  </div>
</div>
