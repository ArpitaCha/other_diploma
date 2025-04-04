<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class StudentMail extends Mailable
{
    use Queueable, SerializesModels;

    private $name; 
    private $form_num; 
    private $email; 

    /**
     * Create a new message instance.
     */
    public function __construct($name,$form_num,$email) // ✅ Pass $mail_data to constructor
    {
        $this->name = $name;
        $this->form_num = $form_num;
        $this->email = $email; // ✅ Assign the passed data
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Form',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.application-mail', // ✅ Ensure this matches your actual view file
            with: [
                'fullname' => $this->name,  // ✅ Use $this->mail_data
                'form_num' => $this->form_num,
                'email' => $this->email,
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

