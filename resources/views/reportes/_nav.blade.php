<div class="tabs mt-2">
  <a href="{{ route('reportes.index') }}"      class="{{ request()->routeIs('reportes.index') ? 'active' : '' }}">General</a> -
  <a href="{{ route('reportes.materiales') }}" class="{{ request()->routeIs('reportes.materiales') ? 'active' : '' }}">Materiales</a> -
  <a href="{{ route('reportes.insumos') }}"    class="{{ request()->routeIs('reportes.insumos') ? 'active' : '' }}">Insumos</a> -
  <a href="{{ route('reportes.clientes') }}"   class="{{ request()->routeIs('reportes.clientes') ? 'active' : '' }}">Clientes</a>
</div>