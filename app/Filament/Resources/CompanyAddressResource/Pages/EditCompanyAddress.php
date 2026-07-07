<?php

namespace App\Filament\Resources\CompanyAddressResource\Pages;

use App\Filament\Resources\CompanyAddressResource;
use App\Services\ShipBubbleService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditCompanyAddress extends EditRecord
{
    protected static string $resource = CompanyAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Company Address updated')
            ->body('The address has been saved successfully.');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $sb = new ShipBubbleService();

        $addressPayload = [
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'address_code'      => $data['address_code'],
        ];

        try {
            $updatedAddress = $sb->updateAddress($addressPayload);

            if (is_string($updatedAddress)) {
                $updatedAddress = json_decode($updatedAddress, true);
            }
            Log::debug('Updated Address: ' . print_r($updatedAddress, true));
            $addressData = $updatedAddress['data'] ?? $updatedAddress;

            $data['address']        = $addressData['formatted_address'] ?? ($data['address'] ?? null);
            $data['state']          = $addressData['state'] ?? ($data['state'] ?? null);
            $data['latitude']       = $addressData['latitude'] ?? ($data['latitude'] ?? null);
            $data['longitude']      = $addressData['longitude'] ?? ($data['longitude'] ?? null);
            $data['city']           = $addressData['city'] ?? ($data['city'] ?? null);
            $data['postal_code']    = $addressData['postal_code'] ?? ($data['postal_code'] ?? null);
            $data['country']        = $addressData['country'] ?? ($data['country'] ?? null);
        } catch (\Exception $e) {
            Log::error('ShipBubble Error: ' . $e->getMessage());
            $this->addError('shipping', $e->getMessage());
        }

        return $data;
    }
}
