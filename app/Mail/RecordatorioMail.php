<?php

namespace App\Mail;

use App\Models\Recordatorio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RecordatorioMail extends Mailable
{
    use Queueable;

    public function __construct(public Recordatorio $recordatorio) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio: '.$this->recordatorio->titulo);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recordatorio');
    }
}
