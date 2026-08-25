<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" level="1">Saved Addresses</flux:heading>
        <flux:button variant="{{ $showForm ? 'ghost' : 'primary' }}" wire:click="$toggle('showForm')">
            {{ $showForm ? 'Cancel' : 'Add Address' }}
        </flux:button>
    </div>

    @if ($showForm)
        <form wire:submit="addAddress" class="grid max-w-2xl gap-4 rounded-xl border border-neutral-200 p-6 sm:grid-cols-2 dark:border-neutral-700">
            <flux:input wire:model="first_name" label="First Name" />
            <flux:input wire:model="last_name" label="Last Name" />
            <flux:input wire:model="phone" label="Phone" class="sm:col-span-2" />
            <flux:input wire:model="street_address" label="Street Address" class="sm:col-span-2" />
            <flux:input wire:model="city" label="City" />
            <flux:input wire:model="state" label="State" />
            <flux:input wire:model="zip_code" label="Postal Code" />
            <flux:input wire:model="country" label="Country" />
            <div class="sm:col-span-2">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">Save Address</flux:button>
            </div>
        </form>
    @endif

    @if ($addresses->isEmpty() && ! $showForm)
        <div class="rounded-xl border border-neutral-200 p-10 text-center dark:border-neutral-700">
            <flux:heading size="lg">No saved addresses</flux:heading>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Add an address to speed up checkout next time.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                    @if ($address->is_default)
                        <flux:badge color="blue" size="sm" class="mb-3">Default</flux:badge>
                    @endif
                    <p class="text-sm font-medium">{{ $address->full_name }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $address->street_address }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $address->city }}, {{ $address->state }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $address->country }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $address->phone }}</p>

                    <div class="mt-4 flex gap-4 text-xs">
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="text-blue-600 hover:underline dark:text-blue-400">Make Default</button>
                        @endunless
                        <button type="button" wire:click="deleteAddress({{ $address->id }})" wire:confirm="Remove this address?" class="text-zinc-500 hover:underline dark:text-zinc-400">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
