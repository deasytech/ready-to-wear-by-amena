<?php

use App\Filament\Resources\CompanyAddressResource\Pages\CreateCompanyAddress;
use App\Filament\Resources\CompanyAddressResource\Pages\EditCompanyAddress;
use App\Models\CompanyAddress;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function shipbubbleAdmin(): User
{
    return User::factory()->create(['email' => 'admin@readytowearbyamena.com']);
}

it('still creates a company address when shipbubble validation fails', function () {
    Http::fake([
        '*/shipping/address/validate' => Http::response(['message' => 'timed out'], 500),
    ]);

    Livewire::actingAs(shipbubbleAdmin())
        ->test(CreateCompanyAddress::class)
        ->fillForm([
            'name' => 'RTW Warehouse',
            'email' => 'warehouse@example.com',
            'phone' => '+2348012345678',
            'address' => '1 Warehouse Road, Lagos',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $address = CompanyAddress::first();

    expect($address)->not->toBeNull();
    expect($address->address_code)->toBeNull();
});

it('self-heals a missing address_code on edit instead of calling updateAddress', function () {
    $address = CompanyAddress::create([
        'name' => 'RTW Warehouse',
        'email' => 'warehouse@example.com',
        'phone' => '+2348012345678',
        'address' => '1 Warehouse Road, Lagos',
        'address_code' => null,
    ]);

    Http::fake([
        '*/shipping/address/validate' => Http::response([
            'status' => 'success',
            'data' => [
                'address_code' => 999,
                'formatted_address' => '1 Warehouse Road, Lagos, Nigeria',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '100001',
                'latitude' => 6.5,
                'longitude' => 3.4,
            ],
        ], 200),
        '*/shipping/address/update' => Http::response(['message' => 'should not be called'], 500),
    ]);

    Livewire::actingAs(shipbubbleAdmin())
        ->test(EditCompanyAddress::class, ['record' => $address->getRouteKey()])
        ->fillForm([
            'name' => $address->name,
            'email' => $address->email,
            'phone' => $address->phone,
            'address' => $address->address,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/shipping/address/validate'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/shipping/address/update'));

    expect((int) $address->fresh()->address_code)->toBe(999);
});

it('calls updateAddress on edit when an address_code already exists', function () {
    $address = CompanyAddress::create([
        'name' => 'RTW Warehouse',
        'email' => 'warehouse@example.com',
        'phone' => '+2348012345678',
        'address' => '1 Warehouse Road, Lagos',
        'address_code' => 555,
    ]);

    Http::fake([
        '*/shipping/address/update' => Http::response([
            'status' => 'success',
            'data' => [
                'formatted_address' => '1 Warehouse Road, Lagos, Nigeria',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
            ],
        ], 200),
    ]);

    Livewire::actingAs(shipbubbleAdmin())
        ->test(EditCompanyAddress::class, ['record' => $address->getRouteKey()])
        ->fillForm([
            'name' => $address->name,
            'email' => $address->email,
            'phone' => $address->phone,
            'address' => $address->address,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/shipping/address/update'));

    expect((int) $address->fresh()->address_code)->toBe(555);
});
