@php
  // helper de valores del producto
  $v = fn($campo, $def = '') => old($campo, isset($producto) ? $producto->{$campo} : $def);

  // seleccionados
  $seleccionados = isset($producto) ? $producto->materiales->keyBy('id_insumo') : collect();

  // ids seleccionados
  $seleccionadosIds = collect(old('materiales', $seleccionados->keys()->all()));

  // recargos tras validación fallida
  $recargosOld = old('recargos', []);

  // recargos al editar
  $recargosEdit = isset($producto)
      ? $producto->materiales->mapWithKeys(fn($m) => [$m->id_insumo => $m->pivot->recargo ?? 0])->all()
      : [];
@endphp

<label>Nombre</label>
<input type="text" name="nombre" value="{{ $v('nombre') }}" required>

<label>Descripción</label>
<textarea name="descripcion">{{ $v('descripcion') }}</textarea>

<div class="form-row">
  <div>
    <label>Precio estimado (base)</label>
    <input type="number" step="0.01" name="precio_estimado" value="{{ $v('precio_estimado') }}">
  </div>
  <div>
    <label>Estado</label>
    @php $est = $v('estado','Activo'); @endphp
    <select name="estado">
      <option value="Activo"   {{ $est==='Activo'?'selected':'' }}>Activo</option>
      <option value="Inactivo" {{ $est==='Inactivo'?'selected':'' }}>Inactivo</option>
    </select>
  </div>
</div>

<label>Foto (opcional)</label>
<input type="file" name="foto" accept="image/*">

@if(isset($producto) && $producto->foto_url)
  <div class="mt-2">
    <small class="muted">Vista previa:</small><br>
    <img src="{{ $producto->foto_url }}" alt="Foto {{ $producto->nombre }}" style="width:260px; height:160px; object-fit:cover; border-radius:8px;">
  </div>
@endif

<h3 class="mt-3">Materiales permitidos</h3>
<p class="muted">Selecciona materiales para este producto y define un recargo opcional en quetzales (Q).</p>

<div class="card mt-2">
  <div class="card-body">
    @if(isset($materiales) && $materiales->count())
      <div class="form-row">
        <div>
          <label>Agregar material</label>
          <select id="material_selector">
            <option value="">— Seleccione —</option>
            @foreach($materiales as $m)
              <option value="{{ $m->id_insumo }}">{{ $m->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="items-end flex">
          <button type="button" class="btn btn-secondary btn-sm" id="add_material">Agregar</button>
        </div>
      </div>

      <div class="mt-2">
        <table class="table" id="mat_table">
          <thead>
            <tr>
              <th>Material</th>
              <th>Recargo (Q)</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($materiales as $m)
              @if($seleccionadosIds->contains($m->id_insumo))
                @php
                  $rid = $m->id_insumo;
                  $valorRecargo = array_key_exists($rid, $recargosOld)
                                    ? $recargosOld[$rid]
                                    : ($recargosEdit[$rid] ?? 0);
                @endphp
                <tr data-id="{{ $rid }}">
                  <td>
                    {{ $m->nombre }}
                    <input type="hidden" name="materiales[]" value="{{ $rid }}">
                  </td>
                  <td>
                    <input type="number" step="0.01" name="recargos[{{ $rid }}]" value="{{ $valorRecargo }}">
                  </td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm js-remove">Quitar</button>
                  </td>
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="alert alert-warn">No hay materiales activos (insumos.categoria = "material").</div>
    @endif
  </div>
</div>

<div class="form-actions">
  <button type="submit" class="btn btn-primary">{{ ($modo ?? '')==='editar' ? 'Actualizar' : 'Guardar' }}</button>
  <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
</div>

<script>
(() => {
  const sel   = document.getElementById('material_selector');
  const add   = document.getElementById('add_material');
  const tbody = document.querySelector('#mat_table tbody');

  if (!sel || !add || !tbody) return;

  function yaExiste(id){ return !!tbody.querySelector(`tr[data-id="${id}"]`); }

  add.addEventListener('click', () => {
    const id = sel.value;
    const text = sel.options[sel.selectedIndex]?.text || '';
    if (!id) return;
    if (yaExiste(id)) return;

    const tr = document.createElement('tr');
    tr.setAttribute('data-id', id);
    tr.innerHTML = `
      <td>${text} <input type="hidden" name="materiales[]" value="${id}"></td>
      <td><input type="number" step="0.01" name="recargos[${id}]" value="0"></td>
      <td><button type="button" class="btn btn-danger btn-sm js-remove">Quitar</button></td>
    `;
    tbody.appendChild(tr);
  });

  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-remove');
    if (!btn) return;
    btn.closest('tr')?.remove();
  });
})();
</script>
