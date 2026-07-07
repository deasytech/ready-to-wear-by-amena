<?php

namespace App\Livewire\Storefront;

use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Newsletter extends Component
{
    #[Validate('required|email:rfc,dns|max:255')]
    public string $email = '';

    public bool $subscribed = false;

    public function subscribe(): void
    {
        $this->validate();

        NewsletterSubscriber::updateOrCreate(
            ['email' => $this->email],
            ['is_active' => true, 'subscribed_at' => now()]
        );

        $this->subscribed = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.storefront.newsletter');
    }
}
