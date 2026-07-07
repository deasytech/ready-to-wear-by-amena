<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockistResource\Pages;
use App\Filament\Resources\StockistResource\RelationManagers;
use App\Models\Stockist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Str;

class StockistResource extends Resource
{
    protected static ?string $model = Stockist::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Stockist Location';
    protected static ?string $pluralModelLabel = 'Stockist Locations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('The slug is used for the URL. Leave empty to auto-generate from name.'),

                                Forms\Components\Textarea::make('description')
                                    ->rows(3)
                                    ->helperText('Brief description of the stockist location.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Location Details')
                            ->schema([
                                Forms\Components\TextInput::make('address')
                                    ->required()
                                    ->maxLength(255),

                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('city')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('state')
                                            ->maxLength(255),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('country')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('postal_code')
                                            ->label('Postal Code')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Section::make('Contact Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->suffixIcon('heroicon-m-globe-alt')
                                    ->maxLength(255),
                            ]),

                        Section::make('Map Coordinates')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('latitude')
                                            ->numeric()
                                            ->suffixIcon('heroicon-m-map-pin')
                                            ->helperText('Optional: For map integration'),

                                        Forms\Components\TextInput::make('longitude')
                                            ->numeric()
                                            ->suffixIcon('heroicon-m-map-pin')
                                            ->helperText('Optional: For map integration'),
                                    ]),
                            ])
                            ->collapsible(),

                        Section::make('Location Image')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('stockist-images')
                                    ->maxSize(2048)
                                    ->helperText('Recommended size: 800x600px, max 2MB')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Settings')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Display this location on the website'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->square()
                    ->size(50)
                    ->label('Image'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn(Stockist $record): string => Str::limit($record->city . ', ' . $record->country, 30)),

                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\SelectFilter::make('country')
                    ->options(function () {
                        return Stockist::select('country')->distinct()->pluck('country', 'country');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\BulkAction::make('activate')
                            ->label('Activate Selected')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->action(fn($records) => $records->each->update(['is_active' => true])),

                        Tables\Actions\BulkAction::make('deactivate')
                            ->label('Deactivate Selected')
                            ->icon('heroicon-o-x-circle')
                            ->color('warning')
                            ->action(fn($records) => $records->each->update(['is_active' => false])),
                    ]),
                ]),
            ])
            ->defaultSort('sort_order')
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockists::route('/'),
            'create' => Pages\CreateStockist::route('/create'),
            'edit' => Pages\EditStockist::route('/{record}/edit'),
        ];
    }
}
