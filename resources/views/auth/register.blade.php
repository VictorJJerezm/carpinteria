@extends('layouts.app')

@section('contenido')
<h1>Crear cuenta</h1>
<p class="muted">Regístrate como cliente para enviar cotizaciones.</p>

@if ($errors->any())
  <div class="alert alert-bad mt-2">
    @foreach ($errors->all() as $e)
      <div>{{ $e }}</div>
    @endforeach
  </div>
@endif

<div class="card mt-3">
  <div class="card-body">
    <form method="POST" action="{{ route('register.store') }}">
      @csrf

      <label>Nombre completo</label>
      <input type="text" name="nombre" value="{{ old('nombre') }}" required>

      <div class="form-row">
        <div>
          <label>Correo</label>
          <input type="email" name="correo" value="{{ old('correo') }}" required>
        </div>
        <div>
          <label>Teléfono (opcional)</label>
          <input type="text" name="telefono" value="{{ old('telefono') }}">
        </div>
      </div>

      <div class="form-row">
        <div>
          <label>Contraseña</label>
          <input type="password" name="contra" required>
        </div>
        <div>
          <label>Confirmar contraseña</label>
          <input type="password" name="contra_confirmation" required>
        </div>
      </div>

      <label class="flex items-center gap-2 mt-2">
        <input type="checkbox" name="acepta" value="1" {{ old('acepta') ? 'checked' : '' }}>
        <span>Acepto los términos y condiciones</span>
      </label>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Crear cuenta</button>
        <a href="{{ route('login') }}" class="btn btn-secondary">Ya tengo cuenta</a>
      </div>
    </form>
  </div>
</div>
@endsection
