<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items', 'address');
        $this->url = \Illuminate\Support\Facades\Route::has('account.orders.show')
            ? route('account.orders.show', $order)
            : url('/');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation #'.$this->order->id.' - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.placed',
            with: ['order' => $this->order, 'url' => $this->url],
        );
    }
}
