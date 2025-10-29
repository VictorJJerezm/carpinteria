<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif">
  <h2>El cliente rechazó la cotización #{{ $c->id_cotizacion }}</h2>
  <p>Cliente: <strong>{{ $c->cliente?->nombre }}</strong> ({{ $c->cliente?->correo }})</p>
  <p>Estado actual: {{ $c->estado }}</p>
  <p>
    <a href="{{ route('gestion.cotizaciones.show',$c->id_cotizacion) }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
      Ver cotización
    </a>
  </p>
</body></html>