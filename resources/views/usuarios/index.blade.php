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
    <a class="brand" href="{{ route('catalogo.index') }}">Carpintería</a>

    {{-- ===== NAV DESKTOP (visible >= 641px) ===== --}}
    <nav class="hide-mobile">
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

          <details class="dropdown {{ $isProduccionActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">Producción</summary>
            <div class="dropdown-menu">
              <a href="{{ route('productos.index') }}"            class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">Productos</a>
              <a href="{{ route('insumos.index') }}"              class="{{ request()->routeIs('insumos.*') ? 'active' : '' }}">Insumos</a>
              <a href="{{ route('inventario.index') }}"           class="{{ request()->routeIs('inventario.*') ? 'active' : '' }}">Inventario</a>
              @if(\Illuminate\Support\Facades\Route::has('proveedores.index'))
                <a href="{{ route('proveedores.index') }}"        class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}">Proveedores</a>
              @else
                <span class="dropdown-item muted" title="Próximamente">Proveedores</span>
              @endif
            </div>
          </details>

          <details class="dropdown {{ $isComercialActive ? 'active' : '' }}">
            <summary class="dropdown-toggle">Pedidos y cotizaciones</summary>
            <div class="dropdown-menu">
              <a href="{{ route('pedidos.index') }}"              class="{{ request()->routeIs('pedidos.*') ? 'active' : '' }}">Pedidos</a>
              <a href="{{ route('gestion.cotizaciones.index') }}" class="{{ request()->routeIs('gestion.cotizaciones.*') ? 'active' : '' }}">Cotizaciones</a>
            </div>
          </details>

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

    <div class="spacer hide-mobile"></div>

    {{-- Acciones (desktop) --}}
    <div class="hide-mobile">
      @auth
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Salir</button>
        </form>
      @endauth
      @guest
        <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Crear cuenta</a>
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Ingresar</a>
      @endguest
    </div>

    {{-- ===== NAV MÓVIL (visible <= 640px) ===== --}}
    <details class="show-mobile" style="margin-left:auto;">
      <summary class="btn btn-secondary btn-sm" aria-label="Abrir menú">☰ Menú</summary>

      <div class="card mt-2" style="width:100%; max-width:100%;">
        <div class="card-body" style="padding:.75rem;">
          <nav style="display:flex; flex-direction:column; gap:.35rem;">
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

                {{-- Menús agrupados (simple) --}}
                <details class="{{ $isProduccionActive ? 'active' : '' }}">
                  <summary class="dropdown-toggle">Producción</summary>
                  <div class="dropdown-menu" style="position:static; box-shadow:none; border:none; padding:0; margin:.25rem 0 0 0;">
                    <a href="{{ route('productos.index') }}"            class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">Productos</a>
                    <a href="{{ route('insumos.index') }}"              class="{{ request()->routeIs('insumos.*') ? 'active' : '' }}">Insumos</a>
                    <a href="{{ route('inventario.index') }}"           class="{{ request()->routeIs('inventario.*') ? 'active' : '' }}">Inventario</a>
                    @if(\Illuminate\Support\Facades\Route::has('proveedores.index'))
                      <a href="{{ route('proveedores.index') }}"        class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}">Proveedores</a>
                    @else
                      <span class="dropdown-item muted" title="Próximamente">Proveedores</span>
                    @endif
                  </div>
                </details>

                <details class="{{ $isComercialActive ? 'active' : '' }}">
                  <summary class="dropdown-toggle">Pedidos y cotizaciones</summary>
                  <div class="dropdown-menu" style="position:static; box-shadow:none; border:none; padding:0; margin:.25rem 0 0 0;">
                    <a href="{{ route('pedidos.index') }}"              class="{{ request()->routeIs('pedidos.*') ? 'active' : '' }}">Pedidos</a>
                    <a href="{{ route('gestion.cotizaciones.index') }}" class="{{ request()->routeIs('gestion.cotizaciones.*') ? 'active' : '' }}">Cotizaciones</a>
                  </div>
                </details>

                <details class="{{ $isReportesActive ? 'active' : '' }}">
                  <summary class="dropdown-toggle">Reportes</summary>
                  <div class="dropdown-menu" style="position:static; box-shadow:none; border:none; padding:0; margin:.25rem 0 0 0;">
                    <a href="{{ route('reportes.index') }}"      class="{{ request()->routeIs('reportes.index') ? 'active' : '' }}">General</a>
                    <a href="{{ route('reportes.materiales') }}" class="{{ request()->routeIs('reportes.materiales') ? 'active' : '' }}">Materiales</a>
                    <a href="{{ route('reportes.insumos') }}"    class="{{ request()->routeIs('reportes.insumos') ? 'active' : '' }}">Insumos</a>
                    <a href="{{ route('reportes.clientes') }}"   class="{{ request()->routeIs('reportes.clientes') ? 'active' : '' }}">Clientes</a>
                  </div>
                </details>
              @endif
            @endauth
          </nav>

          {{-- Acciones (móvil) --}}
          <div class="form-actions" style="margin-top:.6rem;">
            @auth
              <form method="POST" action="{{ route('logout') }}" style="display:inline; width:100%;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm btn-block">Salir</button>
              </form>
            @endauth
            @guest
              <a href="{{ route('register') }}" class="btn btn-secondary btn-sm btn-block">Crear cuenta</a>
              <a href="{{ route('login') }}" class="btn btn-primary btn-sm btn-block">Ingresar</a>
            @endguest
          </div>
        </div>
      </div>
    </details>
    {{-- ===== FIN NAV MÓVIL ===== --}}
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
