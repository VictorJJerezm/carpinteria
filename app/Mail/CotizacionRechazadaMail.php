<?php

namespace App\Mail;

use App\Models\Cotizacion;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CotizacionRechazadaMail extends Mailable
{
    public function __construct(public Cotizacion $c) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'El cliente rechazó la cotización #'.$this->c->id_cotizacion
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cotizaciones.rechazada',
            with: ['c' => $this->c]
        );
    }

    public function attachments(): array { return []; }
}
