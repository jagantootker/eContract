<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Token Pengesahan Tukar Kata Laluan eKontrak',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.password-reset-token',
            with: [
                'token' => $this->token,
            ],
        );
    }
}