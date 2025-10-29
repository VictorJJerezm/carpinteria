@extends('layouts.app')
@section('contenido')
<h1>Editar producto</h1>
@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('productos.update', $producto->id_producto) }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      @include('productos._form', ['modo' => 'editar'])
    </form>
  </div>
</div>
@endsection
