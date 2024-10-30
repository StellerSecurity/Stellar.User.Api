<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data)
    {
        return new Envelope(
            from: new Address($this->data['from'], $this->data['name']),
            subject: 'StellarSecurity.com - Reset Password  ',
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'StellarSecurity.com - Reset Password ',
        );
    }


    public function build()
    {
        return $this->view('mails.resetpasswordlink')->with('data', $this->data);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
