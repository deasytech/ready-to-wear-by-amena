<div class="rtw-container py-16">
    <p class="rtw-label mb-4">My Account</p>
    <div class="mb-10 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-serif text-3xl">Saved Addresses</h1>
        <button type="button" class="rtw-btn-secondary" wire:click="$toggle('showForm')">
            {{ $showForm ? 'Cancel' : 'Add Address' }}
        </button>
    </div>

    <x-account.nav />

    @if ($showForm)
        <form wire:submit="addAddress" class="mt-10 grid max-w-2xl gap-4 border border-neutral-200 p-6 sm:grid-cols-2">
            <div>
                <label for="addr_first_name" class="rtw-label">First Name</label>
                <input type="text" id="addr_first_name" wire:model="first_name" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="addr_last_name" class="rtw-label">Last Name</label>
                <input type="text" id="addr_last_name" wire:model="last_name" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="addr_phone" class="rtw-label">Phone</label>
                <input type="text" id="addr_phone" wire:model="phone" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="addr_street_address" class="rtw-label">Street Address</label>
                <input type="text" id="addr_street_address" wire:model="street_address" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('street_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="addr_city" class="rtw-label">City</label>
                <input type="text" id="addr_city" wire:model="city" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="addr_state" class="rtw-label">State</label>
                <input type="text" id="addr_state" wire:model="state" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="addr_zip_code" class="rtw-label">Postal Code</label>
                <input type="text" id="addr_zip_code" wire:model="zip_code" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label for="addr_country" class="rtw-label">Country</label>
                <input type="text" id="addr_country" wire:model="country" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                @error('country') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rtw-btn-primary" wire:loading.attr="disabled">Save Address</button>
            </div>
        </form>
    @endif

    @if ($addresses->isEmpty() && ! $showForm)
        <x-storefront.empty-state title="No saved addresses" description="Add an address to speed up checkout next time." />
    @else
        <div class="mt-10 grid gap-4 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <div class="border border-neutral-200 p-6">
                    @if ($address->is_default)
                        <span class="rtw-label mb-3 inline-block">Default</span>
                    @endif
                    <p class="text-sm font-medium">{{ $address->full_name }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ $address->street_address }}</p>
                    <p class="text-sm text-neutral-600">{{ $address->city }}, {{ $address->state }}</p>
                    <p class="text-sm text-neutral-600">{{ $address->country }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ $address->phone }}</p>

                    <div class="mt-4 flex gap-4 text-xs">
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="rtw-link-underline">Make Default</button>
                        @endunless
                        <button type="button" wire:click="deleteAddress({{ $address->id }})" wire:confirm="Remove this address?" class="rtw-link-underline text-neutral-500">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
