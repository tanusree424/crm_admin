<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket\Ticket;

class TicketAssignNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable)
    // {
    //     return [
    //         'ticket_id' => $this->ticket->ticket_id,
    //         'title' => $this->ticket->subject,
    //         'category' => $this->ticket->category_id ? $this->ticket->category != null ? $this->ticket->category->name : null : null,
    //         'status' => $this->ticket->status,
    //         'ticketassign' => $this->ticket->myassignuser_id ? 'yes' : 'no',
    //         'ticketassignee_id' => $this->ticket->myassignuser_id,
    //         'overduestatus' => $this->ticket->overduestatus,
    //         'link' => route('admin.ticketshow',$this->ticket->ticket_id),
    //         'clink' => route('loadmore.load_data',$this->ticket->ticket_id),
    //     ];
    // }

public function toDatabase($notifiable)
{
    $toassignusers = DB::table('ticketassignchildren')
        ->where('ticket_id', $this->ticket->id)
        ->pluck('toassignuser_id')
        ->toArray();

    if (empty($toassignusers) && $this->ticket->myassignuser_id) {
        $toassignusers = [$this->ticket->myassignuser_id];
    }

    return [
        'ticket_id' => $this->ticket->ticket_id,
        'title' => $this->ticket->subject,
        'category' => $this->ticket->category_id ? $this->ticket->category?->name : null,
        'toassignuser_id' => $toassignusers,
        'status' => $this->ticket->status,
        'ticketassign' => $this->ticket->myassignuser_id ? 'yes' : 'no',
        'ticketassignee_id' => $this->ticket->myassignuser_id,
        'overduestatus' => $this->ticket->overduestatus,
        'link' => route('admin.ticketshow', $this->ticket->ticket_id),
        'clink' => route('loadmore.load_data', $this->ticket->ticket_id),
        'user_id' => $notifiable->id
    ];
}



}
