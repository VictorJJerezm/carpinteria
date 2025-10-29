@extends('layouts.app')
@section('contenido')
<h1>Nuevo proveedor</h1>

@if ($errors->any())
  <div class="alert alert-bad mt-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('proveedores.store') }}" class="card mt-2">
  @csrf
  <div class="card-body">
    @include('proveedores._form')
    <div class="form-actions">
      <button class="btn btn-primary">Guardar</button>
      <a class="btn btn-secondary" href="{{ route('proveedores.index') }}">Cancelar</a>
    </div>
  </div>
</form>
@endsection