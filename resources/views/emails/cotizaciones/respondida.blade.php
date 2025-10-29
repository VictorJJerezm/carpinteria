<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif">
  <h2>¡Tu cotización #{{ $c->id_cotizacion }} fue respondida!</h2>
  <p>Hola {{ $c->cliente?->nombre }},</p>
  <p>Hemos revisado tu solicitud y te enviamos una propuesta:</p>

  @if(!is_null($c->precio_final))
    <p><strong>Precio final:</strong> Q {{ number_format($c->precio_final,2) }}</p>
  @endif

  @if(!is_null($c->tiempo_estimado_dias))
    <p><strong>Tiempo estimado:</strong> {{ $c->tiempo_estimado_dias }} días</p>
  @endif

  @if($c->respuesta)
    <p><strong>Mensaje:</strong> {{ $c->respuesta }}</p>
  @endif

  <p>
    <a href="{{ route('cliente.cotizaciones.mis') }}" style="display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
      Ver mi cotización
    </a>
  </p>

  <p style="color:#6b7280">Carpintería — Gracias por tu preferencia</p>
</body></html>