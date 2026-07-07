<?php

namespace App\Filament\Resources\CompanyAddressResource\Pages;

use App\Filament\Resources\CompanyAddressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyAddresses extends ListRecords
{
    protected static string $resource = CompanyAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
