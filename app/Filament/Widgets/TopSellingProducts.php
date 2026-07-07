<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellingProducts extends BaseWidget
{
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected static ?int $sort = 4;

    protected static ?string $heading = 'Top Selling Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withSum('orderItems as total_sold', 'quantity')
                    ->having('total_sold', '>', 0)
                    ->orderByDesc('total_sold')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->url(fn (Product $record) => ProductResource::getUrl('edit', ['record' => $record]))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->badge()
                    ->color('success'),
            ]);
    }
}
