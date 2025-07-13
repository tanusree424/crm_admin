<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $ticketId;
    public $note;

    public function __construct($name, $ticketId, $note)
    {
        $this->name = $name;
        $this->ticketId = $ticketId;
        $this->note = $note;
    }

    public function build()
    {
        return $this->subject('Follow-up Confirmation for Ticket #' . $this->ticketId)
            ->view('admin.email.follow-up');
    }
}
