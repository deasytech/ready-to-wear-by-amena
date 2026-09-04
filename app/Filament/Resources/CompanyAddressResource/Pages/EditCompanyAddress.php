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

    /**
     * Re-validate the address with ShipBubble when the admin actually saves
     * changes - not on every page load, which would otherwise fire a write
     * against ShipBubble's API just from opening the edit form.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $sb = new ShipBubbleService();

        // address_code isn't a form field, so it never appears in $data - read
        // it from the underlying record. A record created while ShipBubble was
        // unavailable has no address_code yet, so there's nothing to "update";
        // validate it fresh instead.
        $existingAddressCode = $this->getRecord()->address_code;
        $hasAddressCode = ! empty($existingAddressCode);

        $addressPayload = $hasAddressCode
            ? [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address_code' => $existingAddressCode,
            ]
            : [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ];

        try {
            try {
                $response = $hasAddressCode
                    ? $sb->updateAddress($addressPayload)
                    : $sb->validateAddress($addressPayload);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                // A 404 here means ShipBubble doesn't recognise this
                // address_code at all (e.g. it was created under a
                // different API key/environment than the one now
                // configured) - updating it can never succeed, so treat
                // it as unvalidated and validate the address fresh instead.
                if (! $hasAddressCode || $e->response->status() !== 404) {
                    throw $e;
                }

                $hasAddressCode = false;
                $response = $sb->validateAddress([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]);
            }

            if (is_string($response)) {
                $response = json_decode($response, true);
            }

            $addressData = $response['data'] ?? $response;

            if (! $hasAddressCode) {
                $data['address_code'] = $addressData['address_code'] ?? null;
            }

            $data['address']        = $addressData['formatted_address'] ?? ($data['address'] ?? null);
            $data['state']          = $addressData['state'] ?? ($data['state'] ?? null);
            $data['latitude']       = $addressData['latitude'] ?? ($data['latitude'] ?? null);
            $data['longitude']      = $addressData['longitude'] ?? ($data['longitude'] ?? null);
            $data['city']           = $addressData['city'] ?? ($data['city'] ?? null);
            $data['postal_code']    = $addressData['postal_code'] ?? ($data['postal_code'] ?? null);
            $data['country']        = $addressData['country'] ?? ($data['country'] ?? null);
        } catch (\Exception $e) {
            Log::error('ShipBubble Error on EditCompanyAddress: '.$e->getMessage(), [
                'record_id' => $this->getRecord()->getKey(),
                'address_code' => $existingAddressCode,
                'method' => $hasAddressCode ? 'updateAddress' : 'validateAddress',
            ]);

            Notification::make()
                ->danger()
                ->title('Could not verify this address with ShipBubble')
                ->body('Your changes were saved, but shipping validation failed (the courier API may be temporarily unavailable). Save again to retry.')
                ->send();
        }

        return $data;
    }
}
