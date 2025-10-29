@php
  $v = fn($c,$d='') => old($c, isset($insumo)? $insumo->{$c} : $d);
  $inv = isset($insumo) ? $insumo->inventario : null;
@endphp

<label>Nombre</label>
<input type="text" name="nombre" value="{{ $v('nombre') }}" required>

<label>Descripción</label>
<textarea name="descripcion">{{ $v('descripcion') }}</textarea>

<div class="form-row">
  <div>
    <label>Categoría</label>
    <select name="categoria">
      @php $cat = $v('categoria','material'); @endphp
      <option value="material"   {{ $cat==='material'?'selected':'' }}>Material</option>
      <option value="herramienta"{{ $cat==='herramienta'?'selected':'' }}>Herramienta</option>
      <option value="consumible" {{ $cat==='consumible'?'selected':'' }}>Consumible</option>
    </select>
  </div>
  <div>
    <label>Unidad</label>
    <input type="text" name="unidad" value="{{ $v('unidad','pieza') }}">
  </div>
</div>

<div class="form-row">
  <div><label>Largo</label><input type="number" step="0.01" name="largo" value="{{ $v('largo') }}"></div>
  <div><label>Alto</label><input type="number" step="0.01" name="alto" value="{{ $v('alto') }}"></div>
  <div><label>Ancho</label><input type="number" step="0.01" name="ancho" value="{{ $v('ancho') }}"></div>
</div>

<div class="form-row">
  <div><label>Precio referencia</label><input type="number" step="0.01" name="precio" value="{{ $v('precio', 0) }}" required></div>
  <div>
    <label>Estado</label>
    <select name="estado">
      @php $est = $v('estado','Activo'); @endphp
      <option value="Activo"   {{ $est==='Activo'?'selected':'' }}>Activo</option>
      <option value="Inactivo" {{ $est==='Inactivo'?'selected':'' }}>Inactivo</option>
    </select>
  </div>
</div>

<label>Stock mínimo (alerta)</label>
<input type="number" name="stock_minimo" value="{{ old('stock_minimo', $inv->stock_minimo ?? 5) }}" min="0">

<div class="form-actions">
  <button type="submit" class="btn btn-primary">{{ ($modo ?? '')==='editar' ? 'Actualizar' : 'Guardar' }}</button>
  <a href="{{ route('insumos.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
