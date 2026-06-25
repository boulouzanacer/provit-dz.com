<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientEmailVerificationCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Client $client,
        public string $code,
        public ?\DateTimeInterface $expiresAt = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code de confirmation Pro-Vit',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-verification-code',
        );
    }
}
