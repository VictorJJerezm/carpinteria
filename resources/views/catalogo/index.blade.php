@extends('layouts.app')

@section('contenido')
<h1>Catálogo</h1>
<p class="muted">Haz clic en la imagen para ver detalles.</p>

@php
  $productosJs = $productos->map(function($p){
    return [
      'id'      => $p->id_producto,
      'nombre'  => $p->nombre,
      'desc'    => (string) ($p->descripcion ?? ''),
      'precio'  => (float) ($p->precio_estimado ?? 0),
      'foto'    => (string) ($p->foto_url ?? ''),
      'materials' => $p->materiales->pluck('nombre')->values()->all(),
    ];
  })->values()->all();
@endphp

<div class="grid grid-3 mt-3">
  @forelse($productos as $p)
    <div class="card product-card">
      @if($p->foto_url)
        <button class="thumb js-open-product" data-id="{{ $p->id_producto }}" style="all:unset; cursor:pointer; display:block;">
          <img class="thumb" src="{{ $p->foto_url }}" alt="Foto {{ $p->nombre }}">
        </button>
      @else
        <button class="thumb js-open-product" data-id="{{ $p->id_producto }}" style="all:unset; cursor:pointer;">
          <div class="thumb"></div>
        </button>
      @endif

      <div class="card-body">
        <div class="card-title">{{ $p->nombre }}</div>
        <p class="muted">{{ \Illuminate\Support\Str::limit($p->descripcion, 90) }}</p>
        <div class="meta">
          @if(!is_null($p->precio_estimado))
            <strong>Q {{ number_format($p->precio_estimado,2) }}</strong>
          @else
            <span class="badge badge-neutral">Precio a cotizar</span>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="card"><div class="card-body">No hay productos aún.</div></div>
  @endforelse
</div>

{{-- MODAL simple (una sola imagen) --}}
<div class="modal" id="productModal" aria-hidden="true">
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="pm-title">
    <button class="modal-close" type="button" aria-label="Cerrar">&times;</button>

    <div class="modal-body">
      <div class="grid grid-2">
        <div>
          <div class="gallery-stage">
            <img id="pm-stage" src="" alt="Foto producto">
          </div>
        </div>
        <div>
          <h2 id="pm-title">Producto</h2>
          <div class="muted" id="pm-materials"></div>
          <p class="mt-2" id="pm-desc"></p>
          <p class="mt-2"><strong id="pm-price"></strong></p>
            @auth
              @if(auth()->user()->esCliente())
                <a id="pm-cta" href="{{ route('cliente.cotizar.index') }}" class="btn btn-primary mt-2">
                  Cotizar este producto
                </a>
              @endif
            @endauth
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const DATA = @json($productosJs);
  const MAP = new Map(DATA.map(p => [String(p.id), p]));
  const COTIZAR_URL_BASE = @json(route('cliente.cotizar.index'));
  const modal   = document.getElementById('productModal');
  const closeBtn= modal.querySelector('.modal-close');
  const stage   = document.getElementById('pm-stage');
  const titleEl = document.getElementById('pm-title');
  const descEl  = document.getElementById('pm-desc');
  const priceEl = document.getElementById('pm-price');
  const matsEl  = document.getElementById('pm-materials');
  const ctaBtn  = document.getElementById('pm-cta');

  function openModal(pid){
    const p = MAP.get(String(pid));
    if(!p) return;

    stage.src = p.foto || '';
    titleEl.textContent = p.nombre || 'Producto';
    descEl.textContent  = p.desc || '';
    priceEl.textContent = (p.precio > 0) ? `Precio base: Q ${Number(p.precio).toFixed(2)}` : 'Precio a cotizar';
    matsEl.textContent  = (p.materials && p.materials.length) ? 'Materiales: ' + p.materials.join(', ') : '';

    if (ctaBtn) {
      ctaBtn.href = COTIZAR_URL_BASE + '?producto=' + encodeURIComponent(p.id);
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(){
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-open-product');
    if(btn){ openModal(btn.getAttribute('data-id')); }
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });
})();
</script>
@endsection
