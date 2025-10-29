<?php

namespace App\Mail;

use App\Models\Proveedor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;

class SolicitudCotizacionProveedorMail extends Mailable
{
    public function __construct(
        public Proveedor $proveedor,
        public string    $asunto,
        public string    $mensaje
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
            // si quieres que respondan a la cuenta oficial:
            // from y replyTo toman lo de MAIL_FROM_* por defecto
            replyTo: [ new Address(config('mail.from.address'), config('mail.from.name')) ]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.proveedores.solicitud',
            with: [
                'p'       => $this->proveedor,
                'mensaje' => $this->mensaje,
            ]
        );
    }

    public function attachments(): array { return []; }
}
