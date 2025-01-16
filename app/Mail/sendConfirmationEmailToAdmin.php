<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendConfirmationEmailToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E\' stata inserita una nuova prenotazione',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation_to_admin',
            with: [
                'nome' => isset($this->data['firstname']) ? $this->data['firstname'] : null,
                'cognome' => isset($this->data['lastname']) ? $this->data['lastname'] : null,
                'data_prenotazione' => isset($this->data['date']) ? $this->data['date'] : null,
                'ora_inizio' => isset($this->data['start_time']) ? $this->data['start_time'] : null,
                'ora_fine' => isset($this->data['end_time']) ? $this->data['end_time'] : null,
                'tipo_prenotazione' => isset($this->data['type']) ? ucfirst($this->data['type']) : null,
                'email' => isset($this->data['email']) ? $this->data['email'] : null,
                'telefono' => isset($this->data['phone']) ? $this->data['phone'] : null,
                'indirizzo' => isset($this->data['address']) ? $this->data['address'] : null
            ]
        );
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
