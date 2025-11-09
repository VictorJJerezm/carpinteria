<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name', 'Carpintería') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
  <!-- build {{ now() }} -->
</head>
<body>

<header class="navbar">
  <div class="container row">
    <a class="brand" href="{{ route('catalogo.index') }}" title="Carpintería">Carpintería</a>

    <nav class="scroll-x">
      {{-- Público / todos --}}
      <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.index') ? 'active' : '' }}">Catálogo</a>

      @auth
        {{-- Cliente --}}
        @if(auth()->user()->esCliente())
          <a href="{{ route('cliente.cotizaciones.mis') }}" class="{{ request()->routeIs('cliente.cotizaciones.*') ? 'active' : '' }}">Mis cotizaciones</a>
        @endif

        {{-- Admin / Carpintero --}}
        @if(auth()->user()->esAdmin() || auth()->user()->esCarpintero())
          <a href="{{ route('panel.dashboard') }}" class="{{ request()->routeIs('panel.*') ? 'active' : '' }}">Panel</a>

          @if(auth()->user()->esAdmin())
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuarios</a>
          @endif

          @php
            $isProduccionActive = request()->routeIs('productos.*','insumos.*','inventario.*','proveedores.*');
            $isComercialActive  = request()->routeIs('pedidos.*','gestion.cotizaciones.*');
            $isReportesActive   = request()->routeIs('reportes.*');
          @endphp

          {{-- Dropdown: Producción --}}
          <details class="dropdown {{ $isProduccionActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">Producción</summary>
            <div class="dropdown-menu">
              <a href="{{ route('productos.index') }}"  class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">Productos</a>
              <a href="{{ route('insumos.index') }}"    class="{{ request()->routeIs('insumos.*') ? 'active' : '' }}">Insumos</a>
              <a href="{{ route('inventario.index') }}" class="{{ request()->routeIs('inventario.*') ? 'active' : '' }}">Inventario</a>
              @if(\Illuminate\Support\Facades\Route::has('proveedores.index'))
                <a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}">Proveedores</a>
              @else
                <span class="dropdown-item muted" title="Próximamente">Proveedores</span>
              @endif
            </div>
          </details>

          {{-- Dropdown: Pedidos y cotizaciones --}}
          <details class="dropdown {{ $isComercialActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">Pedidos y cotizaciones</summary>
            <div class="dropdown-menu">
              <a href="{{ route('pedidos.index') }}" class="{{ request()->routeIs('pedidos.*') ? 'active' : '' }}">Pedidos</a>
              <a href="{{ route('gestion.cotizaciones.index') }}" class="{{ request()->routeIs('gestion.cotizaciones.*') ? 'active' : '' }}">Cotizaciones</a>
            </div>
          </details>

          {{-- Dropdown: Reportes --}}
          <details class="dropdown {{ $isReportesActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">Reportes</summary>
            <div class="dropdown-menu">
              <a href="{{ route('reportes.index') }}"      class="{{ request()->routeIs('reportes.index') ? 'active' : '' }}">General</a>
              <a href="{{ route('reportes.materiales') }}" class="{{ request()->routeIs('reportes.materiales') ? 'active' : '' }}">Materiales</a>
              <a href="{{ route('reportes.insumos') }}"    class="{{ request()->routeIs('reportes.insumos') ? 'active' : '' }}">Insumos</a>
              <a href="{{ route('reportes.clientes') }}"   class="{{ request()->routeIs('reportes.clientes') ? 'active' : '' }}">Clientes</a>
            </div>
          </details>
        @endif
      @endauth
    </nav>

    <div class="spacer"></div>

    <div class="nav-auth">
      @auth
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Salir</button>
        </form>
      @endauth

      @guest
        <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Crear cuenta</a>
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Ingresar</a>
      @endguest
    </div>
  </div>  
</header>

<main class="section">
  <div class="container">
    @yield('contenido')
  </div>
</main>

<footer class="footer">
  <div class="container muted">© {{ date('Y') }} Carpintería — Gestión y cotizaciones</div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tables = document.querySelectorAll('table.table.cots');

  tables.forEach(tbl => {
    if (!tbl.parentElement.classList.contains('table-scroll')) {
      const wrap = document.createElement('div');
      wrap.className = 'table-scroll';
      wrap.style.width = '100%';
      wrap.style.overflowX = 'auto';
      wrap.style.webkitOverflowScrolling = 'touch';
      wrap.style.borderRadius = '12px';
      wrap.style.background = 'transparent';

      tbl.parentElement.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    }
    // Fuerza un ancho mínimo
    tbl.style.minWidth = '820px';
    tbl.style.borderCollapse = 'separate';
    tbl.style.borderSpacing = '0';

    tbl.querySelectorAll('th, td').forEach(cell => {
      cell.style.whiteSpace = 'nowrap';
    });

    tbl.querySelectorAll('td[data-label="Detalle"]').forEach(cell => {
      cell.style.whiteSpace = 'normal';
    });
  });

  // Drag-to-scroll (mouse y táctil) para una UX más fluida desde el telefono
  document.querySelectorAll('.table-scroll').forEach(scroller => {
    let isDown = false, startX = 0, scrollLeft = 0;

    const start = (clientX) => {
      isDown = true;
      scroller.classList.add('grabbing');
      startX = clientX - scroller.getBoundingClientRect().left;
      scrollLeft = scroller.scrollLeft;
    };
    const move = (clientX) => {
      if (!isDown) return;
      const x = clientX - scroller.getBoundingClientRect().left;
      const walk = (x - startX);
      scroller.scrollLeft = scrollLeft - walk;
    };
    const end = () => { isDown = false; scroller.classList.remove('grabbing'); };

    // Mouse
    scroller.addEventListener('mousedown', e => { start(e.clientX); });
    scroller.addEventListener('mousemove', e => { move(e.clientX); });
    scroller.addEventListener('mouseleave', end);
    scroller.addEventListener('mouseup', end);

    // Touch
    scroller.addEventListener('touchstart', e => { start(e.touches[0].clientX); }, {passive:true});
    scroller.addEventListener('touchmove',  e => { move(e.touches[0].clientX); }, {passive:true});
    scroller.addEventListener('touchend', end);
  });

  // Header "pegado" cuando hay scroll horizontal en móviles - VJ
  const stickyHeaderCSS = `
    @media (max-width: 768px){
      .table-scroll thead th{
        position: sticky; top: 0; z-index: 1;
        background: #fff7f0; /* Color del header de tabla - VJ */
      }
      .table-scroll.grabbing { cursor: grabbing; }
      .table-scroll { cursor: grab; }
    }
  `;
  const style = document.createElement('style');
  style.appendChild(document.createTextNode(stickyHeaderCSS));
  document.head.appendChild(style);
});
</script>

</body>
</html>
