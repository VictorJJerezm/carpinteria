<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name', 'Carpintería') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>

<header class="navbar">
  <div class="container row">
    <a class="brand" href="{{ route('catalogo.index') }}">Carpintería</a>

    <!-- Botón menú móvil
    <button id="navToggle" class="nav-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="mainNav">
      <span class="nav-toggle-bar"></span>
      <span class="nav-toggle-bar"></span>
      <span class="nav-toggle-bar"></span>
    </button> -->

   <nav id="mainNav" class="main-nav">
    {{-- Público / todos --}}
    <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.index') ? 'active' : '' }}">Catálogo</a>

    @auth
      {{-- Cliente --}}
      @if(auth()->user()->esCliente())
        <a href="{{ route('cliente.cotizaciones.mis') }}" class="{{ request()->routeIs('cliente.cotizaciones.*') ? 'active' : '' }}">Mis cotizaciones</a>
      @endif

      {{-- Admin / Carpintero --}}
      @if(auth()->user()->esAdmin() || auth()->user()->esCarpintero())

        {{-- Top-level: Panel y Reportes para ambos roles --}}
        <a href="{{ route('panel.dashboard') }}" class="{{ request()->routeIs('panel.*') ? 'active' : '' }}">Panel</a>

        {{-- Top-level: Usuarios SOLO admin --}}
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

        {{-- Dropdown: Pedidos y cotizaciones --}}
        <details class="dropdown {{ $isComercialActive ? 'active' : '' }}">
          <summary class="dropdown-toggle">Pedidos y cotizaciones</summary>
          <div class="dropdown-menu">
            <a href="{{ route('pedidos.index') }}"              class="{{ request()->routeIs('pedidos.*') ? 'active' : '' }}">Pedidos</a>
            <a href="{{ route('gestion.cotizaciones.index') }}" class="{{ request()->routeIs('gestion.cotizaciones.*') ? 'active' : '' }}">Cotizaciones</a>
          </div>
        </details>

        {{-- Dropdown de Reportes --}}
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
</header>

<main class="section">
  <div class="container">
    @yield('contenido')
  </div>
</main>

<footer class="footer">
  <div class="container muted">© {{ date('Y') }} Carpintería — Gestión y cotizaciones</div>
</footer>
<!-- <script>
  // Toggle menú móvil
  document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById('navToggle');
    const nav = document.getElementById('mainNav');

    if (btn && nav) {
      btn.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('nav-open', isOpen);
      });
      // Cierra el menú al hacer tap en un enlace
      nav.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
          nav.classList.remove('open');
          document.body.classList.remove('nav-open');
          btn.setAttribute('aria-expanded', 'false');
        });
      });
    }

    // Comportamiento exclusivo de un <details> abierto a la vez (ya lo tenías)
    document.querySelectorAll("details.dropdown").forEach((el) => {
      el.addEventListener("toggle", () => {
        if (el.open) {
          document.querySelectorAll("details.dropdown").forEach((other) => {
            if (other !== el) other.removeAttribute("open");
          });
        }
      });
    });
  });
</script> -->
</body>
</html>