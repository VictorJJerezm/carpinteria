<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif">
  <h2>El cliente aprobó la cotización #{{ $c->id_cotizacion }}</h2>
  <p>Cliente: <strong>{{ $c->cliente?->nombre }}</strong> ({{ $c->cliente?->correo }})</p>
  @if(!is_null($c->precio_final))
    <p><strong>Precio final:</strong> Q {{ number_format($c->precio_final,2) }}</p>
  @endif
  <p>Revisa el pedido generado en el sistema.</p>
  <p>
    <a href="{{ route('gestion.cotizaciones.show',$c->id_cotizacion) }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
      Ver cotización
    </a>
  </p>
</body></html>