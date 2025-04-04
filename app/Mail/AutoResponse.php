<?php

namespace App\Mail;

use App\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoResponse extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $contact;
    public function __construct(ContactUs $contact)
    {
        //
        $this->contact = $contact;
    }


    public function build()
    {
        return $this->view('emails.autoresponse')
                    ->text('emails.autoresp-plain')
                    ->subject("Thank you for contacting us, {$this->contact->name}")
                    ->with([
                        'contactName' => $this->contact->name,
                        'contactSubject' => $this->contact->subject
                    ]);
    }
}
