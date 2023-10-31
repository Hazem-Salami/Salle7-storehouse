<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $contact_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($contact_data)
    {
        $this->contact_data = $contact_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from_name = "Salle7";
        $from_email = "salle7.2023@gmail.com";
        $subject = $this->contact_data['fullname'].": You have a new query";
        return $this->from($from_email, $from_name)
            ->view('Verification')
            ->subject($subject)
            ;
    }
}
