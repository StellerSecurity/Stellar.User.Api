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

    private string $title;

    private string $view_blade;

    public function __construct(public array $data)
    {

        $title = "StellarSecurity.com - Reset Password";
        $this->view_blade = "mails.resetpasswordlink";
        if($data['confirmation_code'] !== null)
        {
            $title = "StellarSecurity.com - Confirmation Code";
            $this->view_blade = "mails.resetpassword-confirmationcode";
        }

        $this->title = $title;

        return new Envelope(
            from: new Address($this->data['from'], $this->data['name']),
            subject: $this->title,
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }


    public function build()
    {
        return $this->view($this->view_blade)->with('data', $this->data);
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
