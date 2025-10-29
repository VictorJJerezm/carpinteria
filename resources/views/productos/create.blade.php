@extends('layouts.app')
@section('contenido')
<h1>Nuevo producto</h1>
@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data">
      @csrf
      @include('productos._form', ['modo' => 'crear'])
    </form>
  </div>
</div>
@endsection
