<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class StudentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

   
    private $form_num; 
    private $email; 
    private $inst_name; 

    /**
     * Create a new message instance.
     */
    public function __construct($form_num,$email,$inst_name)
    {
       
        $this->form_num = $form_num;
        $this->email = $email; 
        $this->inst_name = $inst_name; 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approved By Institute',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
     
        return new Content(
            view: 'email.approved-mail',
            with: [
                'form_num' => $this->form_num,
                'email' => $this->email,
                'inst_name' => $this->inst_name,
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

