<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Services\CurrencyService;
use App\Services\ShipBubbleService;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Tonymans33\VideoOptimizer\Components\VideoOptimizer;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalog Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product information')->schema([
                        Forms\Components\TextInput::make('name')
                            ->live(onBlur: true)
                            ->required()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'edit') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            })
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->fileAttachmentsDirectory('products')
                            ->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Product Images')->schema([
                        Forms\Components\FileUpload::make('images')
                            ->multiple()
                            ->maxFiles(5)
                            ->reorderable()
                            ->directory('products'),
                    ]),
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Product Price')->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price (NGN)')
                            ->required()
                            ->numeric()
                            ->prefix('₦')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, CurrencyService $currencyService) {
                                if (! is_numeric($state)) {
                                    return;
                                }

                                // Auto-calculate prices for other currencies based on exchange rates
                                $currencies = $currencyService->getSupportedCurrencies();
                                foreach ($currencies as $code => $config) {
                                    if ($code === 'NGN') {
                                        continue;
                                    }

                                    $convertedPrice = $currencyService->convert((float) $state, 'NGN', $code);
                                    $fieldName = 'price_'.strtolower($code);
                                    $set($fieldName, number_format($convertedPrice, 2, '.', ''));
                                }
                            }),
                        Forms\Components\TextInput::make('price_usd')
                            ->label('Price (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_gbp')
                            ->label('Price (GBP)')
                            ->numeric()
                            ->prefix('£')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_eur')
                            ->label('Price (EUR)')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_cad')
                            ->label('Price (CAD)')
                            ->numeric()
                            ->prefix('C$')
                            ->placeholder('Auto-calculated from NGN price'),
                        Forms\Components\TextInput::make('price_ghs')
                            ->label('Price (GHS)')
                            ->numeric()
                            ->prefix('GH₵')
                            ->placeholder('Auto-calculated from NGN price'),
                    ])->columns(2),
                    Section::make('Product Video')->schema([
                        VideoOptimizer::make('video')
                            ->label('Product Video')
                            ->disk('public')
                            ->directory('videos')
                            ->maxSize(102400)  // 100MB max
                            ->optimize('medium')  // 'low', 'medium', 'high', or null
                            ->format('mp4'),
                    ]),
                    Section::make('Product Associations')->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->relationship('category', 'name'),
                        Forms\Components\Select::make('colors')
                            ->label('Variant')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->relationship('colors', 'name'),

                        Forms\Components\Select::make('sizes')
                            ->label('Sizes')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->relationship('sizes', 'name'),
                    ]),
                    Section::make('Product Status')->schema([
                        Forms\Components\Toggle::make('in_stock')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\Toggle::make('on_sale'),
                    ]),
                    Section::make('Ship Bubble')->schema([
                        Select::make('package_category_id')
                            ->label('Package Category')
                            ->options(function () {
                                $sb = new ShipBubbleService;
                                $categories = $sb->getPackageCategories();
                                $options = [];
                                if (isset($categories['data']) && is_array($categories['data'])) {
                                    foreach ($categories['data'] as $category) {
                                        $options[$category['category_id']] = $category['category'];
                                    }
                                }

                                return $options;
                            })
                            ->searchable()
                            ->required()
                            ->preload(),
                        Select::make('package_dimension')
                            ->label('Package Box')
                            ->options(function () {
                                $sb = new \App\Services\ShipBubbleService;
                                $boxes = $sb->getPackageDimensions();

                                return collect($boxes)->mapWithKeys(fn ($box) => [
                                    json_encode($box) => $box['name'].' - '
                                        .$box['length'].'x'.$box['width'].'x'.$box['height'].'cm, max '
                                        .$box['max_weight'].'kg',
                                ]);
                            })
                            ->searchable()
                            ->required()
                            ->getOptionLabelUsing(function ($value) {
                                if (! $value) {
                                    return null;
                                }

                                $decoded = json_decode($value, true);

                                return $decoded['name'] ?? $value;
                            })
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $box = json_decode($state, true);
                                $set('box_preview_url', $box['description_image_url'] ?? null);
                            }),
                    ]),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Image'),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state, 2))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
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
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('colors')
                    ->label('Colors')
                    ->relationship('colors', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('sizes')
                    ->relationship('sizes', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sizes.name', 'category.name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
