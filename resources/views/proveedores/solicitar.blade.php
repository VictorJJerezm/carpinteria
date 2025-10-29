@extends('layouts.app')
@section('contenido')
<h1>Solicitar cotización</h1>
<div class="muted">Proveedor: <strong>{{ $proveedor->nombre }}</strong> — {{ $proveedor->correo }}</div>

@if ($errors->any())
  <div class="alert alert-bad mt-2">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('proveedores.solicitud.enviar', $proveedor->id_proveedor) }}" class="card mt-2">
  @csrf
  <div class="card-body">
    <label>Asunto</label>
    <input type="text" name="asunto" value="{{ old('asunto', 'Solicitud de cotización - Carpintería') }}" required>

    <label>Mensaje</label>
    <textarea name="mensaje" rows="12" required>{{ old('mensaje', $plantilla) }}</textarea>

    <label>CC (opcional)</label>
    <input type="email" name="cc" value="{{ old('cc') }}" placeholder="alguien@ejemplo.com">

    <div class="form-actions">
      <button class="btn btn-primary">Enviar</button>
      <a class="btn btn-secondary" href="{{ route('proveedores.index') }}">Cancelar</a>
    </div>
  </div>
</form>
@endsection