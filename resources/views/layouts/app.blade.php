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
      <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.index') ? 'active' : '' }}">
        <span class="hide-mobile">Catálogo</span><span class="show-mobile">Catálogo</span>
      </a>

      @auth
        {{-- Cliente --}}
        @if(auth()->user()->esCliente())
          <a href="{{ route('cliente.cotizaciones.mis') }}" class="{{ request()->routeIs('cliente.cotizaciones.*') ? 'active' : '' }}">
            <span class="hide-mobile">Mis cotizaciones</span><span class="show-mobile">Mis cots.</span>
          </a>
        @endif

        {{-- Admin / Carpintero --}}
        @if(auth()->user()->esAdmin() || auth()->user()->esCarpintero())
          {{-- Panel y Usuarios --}}
          <a href="{{ route('panel.dashboard') }}" class="{{ request()->routeIs('panel.*') ? 'active' : '' }}">
            <span class="hide-mobile">Panel</span><span class="show-mobile">Panel</span>
          </a>

          @if(auth()->user()->esAdmin())
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
              <span class="hide-mobile">Usuarios</span><span class="show-mobile">Usr.</span>
            </a>
          @endif

          @php
            $isProduccionActive = request()->routeIs('productos.*','insumos.*','inventario.*','proveedores.*');
            $isComercialActive  = request()->routeIs('pedidos.*','gestion.cotizaciones.*');
            $isReportesActive   = request()->routeIs('reportes.*');
          @endphp

          {{-- Dropdown: Producción --}}
          <details class="dropdown {{ $isProduccionActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">
              <span class="hide-mobile">Producción</span><span class="show-mobile">Prod.</span>
            </summary>
            <div class="dropdown-menu">
              <a href="{{ route('productos.index') }}"  class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">
                <span class="hide-mobile">Productos</span><span class="show-mobile">Prod.</span>
              </a>
              <a href="{{ route('insumos.index') }}"    class="{{ request()->routeIs('insumos.*') ? 'active' : '' }}">
                <span class="hide-mobile">Insumos</span><span class="show-mobile">Ins.</span>
              </a>
              <a href="{{ route('inventario.index') }}" class="{{ request()->routeIs('inventario.*') ? 'active' : '' }}">
                <span class="hide-mobile">Inventario</span><span class="show-mobile">Inv.</span>
              </a>

              @if(\Illuminate\Support\Facades\Route::has('proveedores.index'))
                <a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                  <span class="hide-mobile">Proveedores</span><span class="show-mobile">Prov.</span>
                </a>
              @else
                <span class="dropdown-item muted" title="Próximamente">
                  <span class="hide-mobile">Proveedores</span><span class="show-mobile">Prov.</span>
                </span>
              @endif
            </div>
          </details>

          {{-- Dropdown: Pedidos y cotizaciones --}}
          <details class="dropdown {{ $isComercialActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">
              <span class="hide-mobile">Pedidos y cotizaciones</span><span class="show-mobile">Pedidos</span>
            </summary>
            <div class="dropdown-menu">
              <a href="{{ route('pedidos.index') }}" class="{{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                <span class="hide-mobile">Pedidos</span><span class="show-mobile">Pedidos</span>
              </a>
              <a href="{{ route('gestion.cotizaciones.index') }}" class="{{ request()->routeIs('gestion.cotizaciones.*') ? 'active' : '' }}">
                <span class="hide-mobile">Cotizaciones</span><span class="show-mobile">Cots.</span>
              </a>
            </div>
          </details>

          {{-- Dropdown: Reportes --}}
          <details class="dropdown {{ $isReportesActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">
              <span class="hide-mobile">Reportes</span><span class="show-mobile">Rep.</span>
            </summary>
            <div class="dropdown-menu">
              <a href="{{ route('reportes.index') }}"      class="{{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                <span class="hide-mobile">General</span><span class="show-mobile">General</span>
              </a>
              <a href="{{ route('reportes.materiales') }}" class="{{ request()->routeIs('reportes.materiales') ? 'active' : '' }}">
                <span class="hide-mobile">Materiales</span><span class="show-mobile">Mat.</span>
              </a>
              <a href="{{ route('reportes.insumos') }}"    class="{{ request()->routeIs('reportes.insumos') ? 'active' : '' }}">
                <span class="hide-mobile">Insumos</span><span class="show-mobile">Ins.</span>
              </a>
              <a href="{{ route('reportes.clientes') }}"   class="{{ request()->routeIs('reportes.clientes') ? 'active' : '' }}">
                <span class="hide-mobile">Clientes</span><span class="show-mobile">Cli.</span>
              </a>
            </div>
          </details>
        @endif
      @endauth
    </nav>

    <div class="spacer"></div>

    @auth
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-secondary btn-sm">
          <span class="hide-mobile">Salir</span><span class="show-mobile">Salir</span>
        </button>
      </form>
    @endauth

    @guest
      <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">
        <span class="hide-mobile">Crear cuenta</span><span class="show-mobile">Crear</span>
      </a>
      <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
        <span class="hide-mobile">Ingresar</span><span class="show-mobile">Entrar</span>
      </a>
    @endguest
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
</body>
</html>
