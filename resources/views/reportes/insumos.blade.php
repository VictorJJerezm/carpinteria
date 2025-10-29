@extends('layouts.app')
@section('contenido')
<h1>Reportes · Insumos (stock)</h1>
@include('reportes._nav')

<div class="form-actions" style="justify-content:flex-end; margin:.25rem 0 .5rem">
  <a class="btn btn-primary btn-sm" href="{{ route('reportes.insumos.export') }}">Exportar CSV</a>
</div>

@php
  $okCount   = $insumos->where('estado_stock','OK')->count();
  $bajoCount = $insumos->where('estado_stock','Bajo')->count();
  $criticos = $insumos->filter(fn($i) => ($i->stock_minimo ?? 0) > 0)
                      ->sortBy('porcentaje')->take(5);
@endphp

<style>
  /* Asegura altura real para Chart.js en modo responsive */
  .chart-box { position: relative; height: 260px; width: 100%; }
</style>

<div class="grid grid-2 mt-2">
  <div class="card"><div class="card-body">
    <h3>Resumen de estado</h3>
    <div class="chart-box"><canvas id="chartEstado"></canvas></div>
  </div></div>

  <div class="card"><div class="card-body">
    <h3>Top 5 más críticos</h3>
    <small class="muted">Porcentaje de stock vs mínimo</small>
    <div class="chart-box"><canvas id="chartCriticos"></canvas></div>
  </div></div>
</div>



<div class="card mt-2"><div class="card-body">
  <table class="table">
    <thead>
      <tr><th>Insumo</th><th>Stock</th><th>Mínimo</th><th>Estado</th><th>% sobre mínimo</th></tr>
    </thead>
    <tbody>
      @forelse($insumos as $i)
        @php $badge = $i->estado_stock==='Bajo' ? 'badge-bad':'badge-ok'; @endphp
        <tr>
          <td>{{ $i->nombre }}</td>
          <td>{{ $i->stock }}</td>
          <td>{{ $i->stock_minimo ?? '—' }}</td>
          <td><span class="badge {{ $badge }}">{{ $i->estado_stock }}</span></td>
          <td>{{ $i->porcentaje !== null ? $i->porcentaje.'%' : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="5">Sin datos.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  window.addEventListener('load', function () {
    // Doughnut: OK vs Bajo
    const ctxE = document.getElementById('chartEstado')?.getContext('2d');
    if (ctxE) {
      new Chart(ctxE, {
        type: 'doughnut',
        data: { labels: ['OK','Bajo'], datasets: [{ data: [{{ $okCount }}, {{ $bajoCount }}] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
      });
    }

    // Barra horizontal: Top críticos
    const labelsLow = @json($criticos->pluck('nombre')->values());
    const valuesLow = @json($criticos->pluck('porcentaje')->values());
    const ctxL = document.getElementById('chartCriticos')?.getContext('2d');
    if (ctxL) {
      new Chart(ctxL, {
        type: 'bar',
        data: { labels: labelsLow, datasets: [{ label: '% sobre mínimo', data: valuesLow }] },
        options: {
          responsive: true, maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: { legend: { display: false } },
          scales: { x: { beginAtZero: true, suggestedMax: 120 } }
        }
      });
    }
  });
</script>

