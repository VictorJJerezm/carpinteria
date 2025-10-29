@php $v = fn($c,$d='')=> old($c, $proveedor->{$c} ?? $d); @endphp

<label>Nombre</label>
<input type="text" name="nombre" value="{{ $v('nombre') }}" required>

<label>Correo</label>
<input type="email" name="correo" value="{{ $v('correo') }}" required>

<div class="form-row">
  <div>
    <label>Teléfono</label>
    <input type="text" name="telefono" value="{{ $v('telefono') }}">
  </div>
  <div>
    <label>Empresa</label>
    <input type="text" name="empresa" value="{{ $v('empresa') }}">
  </div>
</div>

<label>Etiquetas <small class="muted">(separadas por coma)</small></label>
<input type="text" name="etiquetas" value="{{ $v('etiquetas') }}" placeholder="maderas, ferretería, barnices">

<label>Notas</label>
<textarea name="notas" rows="4">{{ $v('notas') }}</textarea>

<label class="mt-1">
  <input type="checkbox" name="activo" value="1" {{ $v('activo', true) ? 'checked' : '' }}>
  Activo
</label>