@extends('layouts.app')
@section('contenido')
<h1>Nuevo usuario</h1>

@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-2"><div class="card-body">
  <form method="POST" action="{{ route('usuarios.store') }}">
    @csrf
    @include('usuarios._form')
    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn btn-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
    </div>
  </form>
</div></div>
@endsection
