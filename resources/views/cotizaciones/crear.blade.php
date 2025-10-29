@extends('layouts.app')

@php
  $seleccionado = $seleccionado ?? (int) request()->query('producto', 0);
@endphp

@section('contenido')
<h1>Nueva Cotización</h1>
<p class="muted">Selecciona un producto, su material y la cantidad.</p>

@if(session('ok')) <div class="alert alert-ok mt-2">{{ session('ok') }}</div> @endif
@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

@php
  $productosJs = $productos->map(function ($p) {
      return [
          'id'        => $p->id_producto,
          'precio'    => (float) ($p->precio_estimado ?? 0),
          'foto'      => (string) ($p->foto_url ?? ''),
          'materiales'=> $p->materiales->map(function ($m) {
              return [
                  'id'      => $m->id_insumo,
                  'nombre'  => $m->nombre,
                  'recargo' => (float) ($m->pivot->recargo ?? 0),
              ];
          })->values()->all(),
      ];
  })->values()->all();
@endphp

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('cliente.cotizar.store') }}">
      @csrf

      <label>Producto</label>
      <select name="id_producto" id="id_producto" required>
        <option value="">— Seleccione —</option>
        @foreach($productos as $p)
          <option
            value="{{ $p->id_producto }}"
            data-precio="{{ $p->precio_estimado ?? 0 }}"
            {{ (int)old('id_producto', $seleccionado) === $p->id_producto ? 'selected' : '' }}
          >
            {{ $p->nombre }}
          </option>
        @endforeach
      </select>

      {{-- Vista previa de foto --}}
      <div class="mt-2">
        <div class="card">
          <div class="card-body">
            <div class="flex items-center gap-2">
              <img id="foto_preview" class="thumb-mini" src="" alt="Foto del producto seleccionado">
              <div class="muted">Vista previa del producto seleccionado.</div>
            </div>
          </div>
        </div>
      </div>

      <label class="mt-2">Material</label>
      <select name="id_material" id="id_material" required disabled>
        <option value="">— Seleccione producto primero —</option>
      </select>

      <label class="mt-2">Cantidad</label>
      <input type="number" name="cantidad" id="cantidad" min="1" value="1" required>

      <div class="mt-2">
        <small class="muted">Precio base: Q <span id="precio_base">0.00</span></small><br>
        <small class="muted">Recargo material: Q <span id="recargo_mat">0.00</span></small><br>
        <strong>Total: Q <span id="total">0.00</span></strong>
      </div>

      <label class="mt-2">Medias o Información relevante</label>
      <textarea name="comentario" rows="3"></textarea>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Enviar</button>
        <a href="{{ route('catalogo.index') }}" class="btn btn-secondary">Cancelar</a>
      </div>

      <label class="mt-2">¿Confirmación de medidas por parte del carpintero?</label>
      <small class="muted">El precio por confirmación de medidas es de Q75.00, si realiza el pedido la confirmación de medidas es de cortesia.</small>
      <div class="form-row">
        @php $cm = old('requiere_confirmacion_medidas', '0'); @endphp
        <label class="flex items-center gap-2">
          <input type="radio" name="requiere_confirmacion_medidas" value="1" {{ $cm === '1' ? 'checked' : '' }}>
          <span>Sí, necesito que se confirmen las medidas.</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="radio" name="requiere_confirmacion_medidas" value="0" {{ $cm === '0' ? 'checked' : '' }}>
          <span>No, me responsabilizo sobre las medidas colocadas.</span>
        </label>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  // Cargamos JSON ya “limpio”
  const DATA = @json($productosJs);

  const map = new Map(DATA.map(p => [String(p.id), p]));
  const selProd = document.getElementById('id_producto');
  const selMat  = document.getElementById('id_material');
  const qty     = document.getElementById('cantidad');
  const baseEl  = document.getElementById('precio_base');
  const recEl   = document.getElementById('recargo_mat');
  const totEl   = document.getElementById('total');
  const imgPrev = document.getElementById('foto_preview');

  const fmt = n => (Number(n)||0).toFixed(2);

  function loadMaterials() {
    selMat.innerHTML = '';
    selMat.disabled = true;
    const p = map.get(selProd.value);
    imgPrev.src = (p && p.foto) ? p.foto : '';
    if (!p) {
      selMat.innerHTML = '<option value="">— Seleccione producto primero —</option>';
      updateTotals();
      return;
    }
    if (!p.materiales || p.materiales.length === 0) {
      selMat.innerHTML = '<option value="">(Este producto no tiene materiales configurados)</option>';
      updateTotals();
      return;
    }
    selMat.disabled = false;
    selMat.appendChild(new Option('— Seleccione —',''));
    p.materiales.forEach(m => {
      const opt = new Option(`${m.nombre} (+Q ${fmt(m.recargo)})`, m.id);
      opt.dataset.recargo = m.recargo;
      selMat.appendChild(opt);
    });
    if (p.materiales.length === 1) {
      selMat.selectedIndex = 1;
    }
    updateTotals();
  }

  function updateTotals() {
    const p = map.get(selProd.value) || {precio:0, materiales:[]};
    const base = Number(p.precio) || 0;
    const opt = selMat.options[selMat.selectedIndex];
    const recargo = opt && opt.dataset && opt.dataset.recargo ? Number(opt.dataset.recargo) : 0;
    const cantidad = Number(qty.value || 0);
    baseEl.textContent = fmt(base);
    recEl.textContent  = fmt(recargo);
    totEl.textContent  = fmt((base + recargo) * cantidad);
  }

  selProd.addEventListener('change', loadMaterials);
  selMat.addEventListener('change', updateTotals);
  qty.addEventListener('input', updateTotals);

  const preselect = @json($seleccionado);
  if (preselect) selProd.value = String(preselect);

  loadMaterials();
})();
</script>
@endsection
