<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;
    private $name; 
    private $form_num; 
    private $email; 
    private $inst_name; 
    private $reg_no; 

    /**
     * Create a new message instance.
     */
    public function __construct($name,$form_num,$email,$inst_name,$reg_no)
    {
        //
        // dd($this->inst_name = $inst_name);
        $this->name = $name;
        $this->form_num = $form_num;
        $this->email = $email;
        $this->inst_name = $inst_name;
        $this->reg_no = $reg_no;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Student Approved Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
     
        return new Content(
            view: 'email.succes-mail',
            with: [
                'fullname' => $this->name, 
                'form_num' => $this->form_num,
                'email' => $this->email,
                'inst_name' => $this->inst_name,
                'reg_no' => $this->reg_no,
            
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
