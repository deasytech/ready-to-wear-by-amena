<?php

namespace App\Livewire\Account;

use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Saved Addresses')]
class Addresses extends Component
{
    public bool $showForm = false;

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|string|max:255')]
    public string $phone = '';

    #[Validate('required|string|max:255')]
    public string $street_address = '';

    #[Validate('required|string|max:255')]
    public string $city = '';

    #[Validate('required|string|max:255')]
    public string $state = '';

    #[Validate('nullable|string|max:20')]
    public string $zip_code = '';

    #[Validate('required|string|max:255')]
    public string $country = 'Nigeria';

    public function addAddress(): void
    {
        $data = $this->validate();

        Auth::user()->addresses()->create([
            ...$data,
            'email' => Auth::user()->email,
            'address_type' => 'shipping',
            'is_default' => Auth::user()->addresses()->whereNull('order_id')->doesntExist(),
        ]);

        $this->reset(['first_name', 'last_name', 'phone', 'street_address', 'city', 'state', 'zip_code']);
        $this->country = 'Nigeria';
        $this->showForm = false;
    }

    public function deleteAddress(Address $address): void
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $address->delete();
    }

    public function makeDefault(Address $address): void
    {
        abort_unless($address->user_id === Auth::id(), 403);

        Auth::user()->addresses()->whereNull('order_id')->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    public function render()
    {
        return view('livewire.account.addresses', [
            'addresses' => Auth::user()->addresses()->whereNull('order_id')->latest()->get(),
        ]);
    }
}
