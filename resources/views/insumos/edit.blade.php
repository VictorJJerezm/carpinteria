@extends('layouts.app')
@section('contenido')
<h1>Editar insumo</h1>
@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('insumos.update', $insumo->id_insumo) }}">
      @csrf @method('PUT')
      @include('insumos._form', ['modo'=>'editar'])
    </form>
  </div>
</div>
@endsection
