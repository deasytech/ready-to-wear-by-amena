<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Product created')
            ->body('The product has been created successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['package_dimension']) && is_array($data['package_dimension'])) {
            $data['package_dimension'] = json_encode($data['package_dimension'], true);
        }

        return $data;
    }
}
