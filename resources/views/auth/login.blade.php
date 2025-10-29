@extends('layouts.app')

@section('contenido')
<div class="auth-center">
  <div class="card">
    <div class="card-body">
      <h1>Iniciar sesión</h1>
      <p class="muted">Ingrese sus credenciales para continuar.</p>

      @if (session('info'))
        <div class="alert alert-warn mt-2">{{ session('info') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-bad mt-2">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('login.procesar') }}" class="mt-3">
        @csrf
        <label>Correo</label>
        <input type="email" name="correo" value="{{ old('correo') }}" required>

        <label>Contraseña</label>
        <input type="password" name="contra" required>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Entrar</button>
          <a href="{{ route('register') }}" class="btn btn-secondary">Crear cuenta</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection