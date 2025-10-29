<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif">
  <p>Para: <strong>{{ $p->nombre }}</strong> ({{ $p->empresa ?? 'Proveedor' }})</p>
  <pre style="white-space:pre-wrap; font-family:inherit; line-height:1.5">{{ $mensaje }}</pre>
  <p style="color:#6b7280">Enviado desde Carpintería</p>
</body></html>