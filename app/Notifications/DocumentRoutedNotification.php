<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Document;

class DocumentRoutedNotification extends Notification
{
    public $document;
    public $sender;
    public $recipient;

    public function __construct(Document $document, $sender = null, $recipient = null)
    {
        $this->document = $document;
        $this->sender = $sender;
        $this->recipient = $recipient;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Store in database and send email
    }

    public function toDatabase($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'message' => "Document '{$this->document->title}' has been routed to you",
            'type' => 'document_routed',
            'read_at' => null,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Document: {$this->document->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new document has been routed to you.")
            ->line("Document: {$this->document->title}")
            ->line("Priority: {$this->document->priority}")
            ->line("Status: {$this->document->status}")
            ->action('View Document', url(route('track.detail', $this->document->id)))
            ->line('Thank you for using NAAP Routing System!');
    }
}
