<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\ProductVariant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Low Stock';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductVariant::query()
                    ->with('product', 'color', 'size')
                    ->where('is_active', true)
                    ->where('stock', '<=', 5)
                    ->orderBy('stock')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->url(fn (ProductVariant $record) => ProductResource::getUrl('edit', ['record' => $record->product_id]))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning'),
            ]);
    }
}
