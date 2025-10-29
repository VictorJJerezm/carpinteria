@extends('layouts.app')

@section('contenido')
<h1>Panel</h1>
<p class="muted">Bienvenido, {{ auth()->user()->nombre }}.</p>

<div class="grid grid-3 mt-3">
  <a class="card" href="{{ route('usuarios.index') }}"><div class="card-body"><h3>Gestión de Usuarios</h3><p class="muted">Gestión de Usuarios</p></div></a>
  <a class="card" href="{{ route('productos.index') }}"><div class="card-body"><h3>Productos</h3><p class="muted">Gestiona el catálogo</p></div></a>
  <a class="card" href="{{ route('insumos.index') }}"><div class="card-body"><h3>Insumos</h3><p class="muted">CRUD de insumos</p></div></a>
  <a class="card" href="{{ route('inventario.index') }}"><div class="card-body"><h3>Inventario</h3><p class="muted">Entradas / Salidas</p></div></a>
  <a class="card" href="{{ route('proveedores.index') }}"><div class="card-body"><h3>Proveedores</h3><p class="muted">Contactar proveedores</p></div></a>
  <a class="card" href="{{ route('pedidos.index') }}"><div class="card-body"><h3>Pedidos</h3><p class="muted">Ver pedidos</p></div></a>
  <a class="card" href="{{ route('gestion.cotizaciones.index') }}"><div class="card-body"><h3>Cotizaciones</h3><p class="muted">Ver cotizaciones</p></div></a>
  <a class="card" href="{{ route('reportes.index') }}"><div class="card-body"><h3>Reportes</h3><p class="muted">Metricas y Reportería</p></div></a>
</div>
@endsection
