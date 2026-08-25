<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Services\CurrencyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Stock Variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('color_id')
                    ->label('Color')
                    ->relationship('color', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('size_id')
                    ->label('Size')
                    ->relationship('size', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Section::make('Price Override (optional)')
                    ->description('Leave blank to use the product price for this currency. Setting NGN auto-fills the others from the exchange rate; each can still be edited individually.')
                    ->schema([
                        Forms\Components\TextInput::make('price_override')
                            ->label('Price (NGN)')
                            ->numeric()
                            ->prefix('₦')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, CurrencyService $currencyService) {
                                if (! is_numeric($state)) {
                                    return;
                                }

                                foreach ($currencyService->getSupportedCurrencies() as $code => $config) {
                                    if ($code === 'NGN') {
                                        continue;
                                    }

                                    $converted = $currencyService->convert((float) $state, 'NGN', $code);
                                    $set('price_override_'.strtolower($code), number_format($converted, 2, '.', ''));
                                }
                            }),
                        Forms\Components\TextInput::make('price_override_usd')
                            ->label('Price (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_override_gbp')
                            ->label('Price (GBP)')
                            ->numeric()
                            ->prefix('£')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_override_eur')
                            ->label('Price (EUR)')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_override_cad')
                            ->label('Price (CAD)')
                            ->numeric()
                            ->prefix('C$')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_override_ghs')
                            ->label('Price (GHS)')
                            ->numeric()
                            ->prefix('GH₵')
                            ->placeholder('Auto-calculated from NGN price'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('size.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('price_override')
                    ->label('Price Override')
                    ->formatStateUsing(function ($state, $record) {
                        $overrides = collect([
                            'NGN' => ['₦', $record->price_override],
                            'USD' => ['$', $record->price_override_usd],
                            'GBP' => ['£', $record->price_override_gbp],
                            'EUR' => ['€', $record->price_override_eur],
                            'CAD' => ['C$', $record->price_override_cad],
                            'GHS' => ['GH₵', $record->price_override_ghs],
                        ])->filter(fn ($pair) => $pair[1] !== null);

                        if ($overrides->isEmpty()) {
                            return '—';
                        }

                        return $overrides->map(fn ($pair, $code) => "{$pair[0]}".number_format($pair[1], 2)." {$code}")->implode(' / ');
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
