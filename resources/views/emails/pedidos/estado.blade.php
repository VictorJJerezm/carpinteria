<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif">
  <h2>Tu pedido #{{ $p->id_pedido }} cambió de estado</h2>
  <p>Nuevo estado: <strong>{{ $p->estado }}</strong></p>
  <p>Total: <strong>Q {{ number_format($p->total,2) }}</strong></p>
  <p>
    <a href="{{ route('cliente.cotizaciones.mis') }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
      Ver mis cotizaciones/pedidos
    </a>
  </p>
  <p style="color:#6b7280">Carpintería</p>
</body></html>