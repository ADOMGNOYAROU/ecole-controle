<?php

namespace App\Mail;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelPaiementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Paiement $paiement) {}

    public function envelope(): Envelope
    {
        $enRetard = $this->paiement->estEnRetard();

        return new Envelope(
            subject: $enRetard
                ? 'Paiement en retard — '.$this->paiement->eleve->nomComplet()
                : 'Rappel : échéance de paiement à venir — '.$this->paiement->eleve->nomComplet(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rappel-paiement');
    }
}
