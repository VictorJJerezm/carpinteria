@extends('layouts.app')
@section('contenido')
<h1>Editar usuario</h1>

@if($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-2"><div class="card-body">
  <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
    @csrf @method('PUT')
    @include('usuarios._form', ['modo' => 'editar'])
    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn btn-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
    </div>
  </form>
</div></div>
@endsection
