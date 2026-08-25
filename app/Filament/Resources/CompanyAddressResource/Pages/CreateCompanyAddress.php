<?php

namespace App\Filament\Resources\CompanyAddressResource\Pages;

use App\Filament\Resources\CompanyAddressResource;
use App\Services\ShipBubbleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateCompanyAddress extends CreateRecord
{
    protected static string $resource = CompanyAddressResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Company Address created')
            ->body('The address has been created successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sb = new ShipBubbleService();

        $addressPayload = [
            'name'      => $data['name'] ?? null,
            'email'     => $data['email'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'address'   => $data['address'] ?? null,
        ];

        try {
            $validatedAddress = $sb->validateAddress($addressPayload);

            if (is_string($validatedAddress)) {
                $validatedAddress = json_decode($validatedAddress, true);
            }

            $addressData = $validatedAddress['data'] ?? $validatedAddress;

            $data['address_code']   = $addressData['address_code'] ?? null;
            $data['address']        = $addressData['formatted_address'] ?? ($data['address'] ?? null);
            $data['state']          = $addressData['state'] ?? ($data['state'] ?? null);
            $data['latitude']       = $addressData['latitude'] ?? ($data['latitude'] ?? null);
            $data['longitude']      = $addressData['longitude'] ?? ($data['longitude'] ?? null);
            $data['city']           = $addressData['city'] ?? ($data['city'] ?? null);
            $data['postal_code']    = $addressData['postal_code'] ?? ($data['postal_code'] ?? null);
            $data['country']        = $addressData['country'] ?? ($data['country'] ?? null);
        } catch (\Exception $e) {
            Log::error('ShipBubble Error: '.$e->getMessage());

            // Still let the address save without a ShipBubble address_code -
            // the admin's data entry shouldn't be lost over a courier API
            // hiccup. Editing the record afterwards will retry validation.
            Notification::make()
                ->danger()
                ->title('Could not verify this address with ShipBubble')
                ->body('The address was saved, but shipping validation failed (the courier API may be temporarily unavailable). Open and save it again to retry.')
                ->send();
        }

        $data['user_id'] = Auth::id();

        return $data;
    }
}
