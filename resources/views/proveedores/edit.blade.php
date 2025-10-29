@extends('layouts.app')
@section('contenido')
<h1>Editar proveedor</h1>

@if ($errors->any())
  <div class="alert alert-bad mt-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('proveedores.update', $proveedor->id_proveedor) }}" class="card mt-2">
  @csrf @method('PUT')
  <div class="card-body">
    @include('proveedores._form')
    <div class="form-actions">
      <button class="btn btn-primary">Actualizar</button>
      <a class="btn btn-secondary" href="{{ route('proveedores.index') }}">Cancelar</a>
    </div>
  </div>
</form>
@endsection