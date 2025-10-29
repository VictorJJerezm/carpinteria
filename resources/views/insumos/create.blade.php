@extends('layouts.app')
@section('contenido')
<h1>Nuevo insumo</h1>
@if ($errors->any()) <div class="alert alert-bad mt-2">{{ $errors->first() }}</div> @endif

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('insumos.store') }}">
      @csrf
      @include('insumos._form', ['modo'=>'crear'])
    </form>
  </div>
</div>
@endsection
