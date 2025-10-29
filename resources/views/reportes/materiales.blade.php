@extends('layouts.app')
@section('contenido')
<h1>Reportes · Materiales</h1>
@include('reportes._nav')

<div class="card mt-2"><div class="card-body">
  <form method="GET" class="form-row">
    <div><label>Desde</label><input type="date" name="desde" value="{{ $desde }}"></div>
    <div><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta }}"></div>
    <div class="items-end flex"><button class="btn btn-secondary btn-sm" type="submit">Aplicar</button></div>
    <div class="items-end flex" style="margin-left:auto">
      <a class="btn btn-primary btn-sm" href="{{ route('reportes.materiales.export', compact('desde','hasta')) }}">Exportar CSV</a>
    </div>
  </form>
</div></div>

<div class="grid grid-2 mt-2">
  <div class="card"><div class="card-body">
    <h3>Consumo (Q) por mes <small class="muted">(según pedidos)</small></h3>
    <canvas id="chartMat" width="600" height="280"></canvas>
  </div></div>
  <div class="card"><div class="card-body">
    <h3>Top materiales</h3>
    <table class="table">
      <thead><tr><th>Material</th><th>Cant.</th><th>Subtotal</th></tr></thead>
      <tbody>
        @forelse($top as $t)
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
  const meses = @json($meses);
  const tot   = @json($totMes);
  const ctx = document.getElementById('chartMat').getContext('2d');
  new Chart(ctx, { type:'line', data:{ labels: meses, datasets:[{ label:'Q', data: tot, tension:.3 }] },
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }});
})();
</script>
@endsection
