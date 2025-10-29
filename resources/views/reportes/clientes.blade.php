@extends('layouts.app')
@section('contenido')
<h1>Reportes · Clientes</h1>
@include('reportes._nav')

<div class="card mt-2"><div class="card-body">
  <form method="GET" class="form-row">
    <div><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
    <div><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
    <div class="items-end flex"><button class="btn btn-secondary btn-sm" type="submit">Aplicar</button></div>
    <div class="items-end flex" style="margin-left:auto">
      <a class="btn btn-primary btn-sm" href="{{ route('reportes.clientes.export', compact('desde','hasta')) }}">Exportar CSV</a>
    </div>
  </form>
</div></div>

<div class="card mt-2"><div class="card-body">
  <table class="table">
    <thead>
      <tr><th>Cliente</th><th>Correo</th><th>Cotizaciones</th><th>Aprobadas</th><th>Pedidos</th><th>Monto (Q)</th></tr>
    </thead>
    <tbody>
      @forelse($clientes as $c)
        <tr>
          <td>{{ $c->nombre }}</td>
          <td class="muted">{{ $c->correo }}</td>
          <td>{{ $c->cot_total }}</td>
          <td>{{ $c->cot_aprobadas }}</td>
          <td>{{ $c->pedidos }}</td>
          <td><strong>{{ number_format($c->monto,2) }}</strong></td>
        </tr>
      @empty
        <tr><td colspan="6">Sin datos.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection