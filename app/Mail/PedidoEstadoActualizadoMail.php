<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PedidoEstadoActualizadoMail extends Mailable
{
    public function __construct(public Pedido $p) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu pedido #'.$this->p->id_pedido.' ahora está: '.$this->p->estado
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedidos.estado',
            with: ['p' => $this->p]
        );
    }

    public function attachments(): array { return []; }
}
