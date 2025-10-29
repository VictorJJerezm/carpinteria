<?php

namespace App\Mail;

use App\Models\Cotizacion;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CotizacionRespondidaMail extends Mailable
{
    public function __construct(public Cotizacion $c) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cotización #'.$this->c->id_cotizacion.' ha sido respondida'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cotizaciones.respondida',
            with: ['c' => $this->c]
        );
    }

    public function attachments(): array { return []; }
}
