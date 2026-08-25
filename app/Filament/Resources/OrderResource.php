<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\ShipBubbleService;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Guest checkout'),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'paystack' => 'Paystack',
                                'cod' => 'Cash on Delivery',
                            ])
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options(Order::PAYMENT_STATUSES)
                            ->native(false)
                            ->required()
                            ->default('pending'),
                        Forms\Components\ToggleButtons::make('status')
                            ->options(Order::STATUSES)
                            ->colors([
                                'pending' => 'gray',
                                'confirmed' => 'primary',
                                'processing' => 'warning',
                                'shipped' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                'refunded' => 'danger',
                            ])
                            ->icons([
                                'pending' => 'heroicon-m-clock',
                                'confirmed' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                                'refunded' => 'heroicon-m-arrow-uturn-left',
                            ])
                            ->inline()
                            ->default('pending')
                            ->formatStateUsing(fn ($state) => strtolower($state))
                            ->dehydrateStateUsing(fn ($state) => strtolower($state))
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'NGN' => 'Naira',
                                'USD' => 'US Dollar',
                                'EUR' => 'Euro',
                                'GBP' => 'British Pound',
                            ])
                            ->native(false)
                            ->required()
                            ->default('NGN'),
                        Forms\Components\Select::make('shipping_method_id')
                            ->label('Shipping Method')
                            ->relationship('shippingMethod', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Order Items')->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0))
                                    ->columnSpan(4),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount')))
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('unit_amount')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('total_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3),
                            ])->columns(12),
                        Placeholder::make('grand_total_placeholder')
                            ->label('Grand Total')
                            ->content(function (Get $get, Set $set) {
                                $total = 0;
                                if (! $repeaters = $get('items')) {
                                    return $total;
                                }

                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get("items.$key.total_amount") ?? 0;
                                }

                                $set('grand_total', $total);

                                return '₦'.number_format($total, 2);
                            }),
                        Hidden::make('grand_total')
                            ->default(0),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Order Information')
                    ->schema([
                        TextEntry::make('id')->label('Order #'),
                        TextEntry::make('user.name')->label('Customer')->placeholder('Guest'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('payment_method'),
                        TextEntry::make('payment_status')->badge(),
                        TextEntry::make('currency'),
                        TextEntry::make('shippingMethod.name')
                            ->label('Shipping Method')
                            ->formatStateUsing(fn ($state, $record) => $state ?? $record->shipping_method)
                            ->placeholder('—'),
                        TextEntry::make('grand_total')->money(fn ($record) => $record->currency ?? 'NGN'),
                        TextEntry::make('notes')->columnSpanFull()->placeholder('—'),
                    ])
                    ->columns(3),
                InfolistSection::make('Shipment')
                    ->schema([
                        TextEntry::make('shipment_id')
                            ->label('ShipBubble Shipment')
                            ->placeholder('Not booked yet'),
                        TextEntry::make('tracking_url')
                            ->label('Tracking')
                            ->placeholder('—')
                            ->url(fn ($record) => $record->tracking_url)
                            ->openUrlInNewTab()
                            ->formatStateUsing(fn ($state) => $state ? 'Track shipment' : null),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => filled($record->shipbubble_request_token))
                    ->headerActions([
                        InfolistAction::make('bookShipment')
                            ->label('Book shipment')
                            ->icon('heroicon-m-truck')
                            ->visible(fn ($record) => blank($record->shipment_id) && filled($record->shipbubble_request_token))
                            ->action(function ($record) {
                                app(OrderService::class)->bookShipment($record, app(ShipBubbleService::class));

                                $record->refresh();

                                Notification::make()
                                    ->title($record->shipment_id ? 'Shipment booked' : 'Booking failed')
                                    ->body($record->shipment_id ? "Tracking: {$record->tracking_url}" : 'Check the logs for details - the courier API may be unavailable or the rate quote may have expired.')
                                    ->status($record->shipment_id ? 'success' : 'danger')
                                    ->send();
                            }),
                    ]),
                InfolistSection::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Split::make([
                                    ImageEntry::make('product.first_image')
                                        ->label('')
                                        ->size(64)
                                        ->square(),
                                    TextEntry::make('name')
                                        ->label('Product')
                                        ->html()
                                        ->formatStateUsing(function ($state, $record) {
                                            $meta = collect([
                                                $record->size ? "Size: {$record->size}" : null,
                                                $record->color ? "Color: {$record->color}" : null,
                                                $record->sku ? "SKU: {$record->sku}" : null,
                                            ])->filter()->implode(' · ');

                                            $html = '<span class="font-semibold">'.e($state).'</span>';

                                            if ($meta !== '') {
                                                $html .= '<span class="block text-xs text-gray-500">'.e($meta).'</span>';
                                            }

                                            return $html;
                                        }),
                                    TextEntry::make('quantity')->label('Qty'),
                                    TextEntry::make('unit_amount')->label('Unit Price')->money(fn ($record) => $record->order->currency ?? 'NGN'),
                                    TextEntry::make('total_amount')->label('Total')->money(fn ($record) => $record->order->currency ?? 'NGN'),
                                ])->from('md'),
                            ])
                            ->contained(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_amount')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shippingMethod.name')
                    ->label('Shipping')
                    ->formatStateUsing(fn ($state, $record) => $state ?? $record->shipping_method)
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipment_id')
                    ->label('Shipment')
                    ->placeholder('Not booked')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\SelectColumn::make('status')
                    ->options(Order::STATUSES),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Order::STATUSES),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(Order::PAYMENT_STATUSES),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
