@extends('layouts.app')

@section('contenido')
<h1>Reportes</h1>
@include('reportes._nav')

<div class="card mt-2">
  <div class="card-body">
    <form method="GET" class="form-row">
      <div>
        <label>Desde</label>
        <input type="date" name="desde" value="{{ $desde }}">
      </div>
      <div>
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ $hasta }}">
      </div>
      <div class="items-end flex">
        <button class="btn btn-secondary btn-sm" type="submit">Aplicar</button>
      </div>
      <div class="items-end flex" style="margin-left:auto">
        <a class="btn btn-primary btn-sm" href="{{ route('reportes.export', compact('desde','hasta')) }}">Exportar ingresos (CSV)</a>
      </div>
    </form>
  </div>
</div>

{{-- KPIs --}}
<div class="grid grid-3 mt-2">
  <div class="card"><div class="card-body">
    <h3>Cotizaciones</h3>
    <div class="muted">Total: {{ $cTotal }}</div>
    <div class="mt-1">Pendientes: <strong>{{ $cPend }}</strong></div>
    <div>Respondidas: <strong>{{ $cResp }}</strong></div>
    <div>Aprobadas: <strong>{{ $cApr }}</strong></div>
    <div>Rechazadas: <strong>{{ $cRech }}</strong></div>
    <div>Canceladas: <strong>{{ $cCanc }}</strong></div>
    <div class="mt-1">Tasa aprobación (sobre respondidas): <strong>{{ $tasaAprob }}%</strong></div>
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Pedidos</h3>
    <div class="muted">Total: {{ $pTot }}</div>
    <div class="mt-1">En proceso: <strong>{{ $pProc }}</strong></div>
    <div>Terminado: <strong>{{ $pTerm }}</strong></div>
    <div>Entregado: <strong>{{ $pEnt }}</strong></div>
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Ingresos</h3>
    <div class="muted">Rango: {{ $desde }} → {{ $hasta }}</div>
    <div class="mt-2" style="font-size:1.4rem"><strong>Q {{ number_format($ingresosTotales,2) }}</strong></div>
  </div></div>
</div>

{{-- Gráficas --}}
<div class="grid grid-2 mt-2">
  <div class="card"><div class="card-body">
    <h3>Ingresos por mes</h3>
    <canvas id="chartIngresos" width="600" height="280"></canvas>
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Distribución de cotizaciones</h3>
    <canvas id="chartCot" width="600" height="280"></canvas>
  </div></div>
</div>

{{-- Top listas --}}
<div class="grid grid-2 mt-2">
  <div class="card"><div class="card-body">
    <h3>Top productos</h3>
    <table class="table">
      <thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th></tr></thead>
      <tbody>
      @forelse($topProductos as $t)
        <tr>
          <td>{{ $t->nombre }}</td>
          <td>{{ $t->cantidad }}</td>
          <td>Q {{ number_format($t->subtotal,2) }}</td>
        </tr>
      @empty
        <tr><td colspan="3">Sin datos.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Top materiales</h3>
    <table class="table">
      <thead><tr><th>Material</th><th>Cant.</th><th>Subtotal</th></tr></thead>
      <tbody>
      @forelse($topMateriales as $t)
        <tr>
          <td>{{ $t->nombre }}</td>
          <td>{{ $t->cantidad }}</td>
          <td>Q {{ number_format($t->subtotal,2) }}</td>
        </tr>
      @empty
        <tr><td colspan="3">Sin datos.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>
</div>

<div class="card mt-2"><div class="card-body">
  <h3>Clientes con más cotizaciones</h3>
  <table class="table">
    <thead><tr><th>Cliente</th><th>Correo</th><th># Cotizaciones</th></tr></thead>
    <tbody>
    @forelse($topClientes as $t)
      <tr>
        <td>{{ $t->nombre }}</td>
        <td class="muted">{{ $t->correo }}</td>
        <td><strong>{{ $t->total }}</strong></td>
      </tr>
    @empty
      <tr><td colspan="3">Sin datos.</td></tr>
    @endforelse
    </tbody>
  </table>
</div></div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  (() => {
    const meses = @json($meses);
    const ingresos = @json($ingresos);

    const ctx1 = document.getElementById('chartIngresos').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: meses,
        datasets: [{
          label: 'Ingresos (Q)',
          data: ingresos,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false }},
        scales: { y: { beginAtZero: true } }
      }
    });

    const cotData = {
      labels: ['Pendiente','Respondida','Aprobada','Rechazada','Cancelada'],
      values: [{{ $cPend }}, {{ $cResp }}, {{ $cApr }}, {{ $cRech }}, {{ $cCanc }}]
    };

    const ctx2 = document.getElementById('chartCot').getContext('2d');
    new Chart(ctx2, {
      type: 'bar',
      data: {
        labels: cotData.labels,
        datasets: [{ label: 'Cotizaciones', data: cotData.values }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false }},
        scales: { y: { beginAtZero: true } }
      }
    });
  })();
</script>
@endsection