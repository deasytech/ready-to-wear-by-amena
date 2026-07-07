<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutPageResource\Pages;
use App\Filament\Resources\AboutPageResource\RelationManagers;
use App\Models\About;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AboutPageResource extends Resource
{
  protected static ?string $model = About::class;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';

  protected static ?string $navigationGroup = 'Content Management';

  protected static ?int $navigationSort = 3;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\TextInput::make('section_name')
              ->required()
              ->maxLength(255)
              ->unique(ignoreRecord: true)
              ->helperText('Unique identifier for this section (e.g., main_about, our_story, our_culture)'),

            Forms\Components\TextInput::make('title')
              ->maxLength(255)
              ->helperText('Optional title for the section'),

            Forms\Components\FileUpload::make('image_path')
              ->image()
              ->directory('about-page')
              ->maxSize(2048)
              ->helperText('Optional image for the section'),

            Forms\Components\RichEditor::make('content')
              ->columnSpanFull()
              ->helperText('Main content for this section'),

            Forms\Components\TextInput::make('sort_order')
              ->numeric()
              ->default(0)
              ->helperText('Order in which sections appear (lower numbers first)'),

            Forms\Components\Toggle::make('is_active')
              ->default(true)
              ->inline(false)
              ->helperText('Whether this section should be displayed on the website'),
          ])
          ->columns(2),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('section_name')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('title')
          ->searchable()
          ->limit(50),

        Tables\Columns\ImageColumn::make('image_path')
          ->label('Image')
          ->circular(),

        Tables\Columns\TextColumn::make('content')
          ->limit(100)
          ->html()
          ->wrap(),

        Tables\Columns\TextColumn::make('sort_order')
          ->sortable(),

        Tables\Columns\IconColumn::make('is_active')
          ->boolean()
          ->sortable(),

        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\TernaryFilter::make('is_active')
          ->label('Active')
          ->boolean(),
      ])
      ->actions([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ])
      ->defaultSort('sort_order', 'asc');
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
      'index' => Pages\ListAboutPages::route('/'),
      'create' => Pages\CreateAboutPage::route('/create'),
      'edit' => Pages\EditAboutPage::route('/{record}/edit'),
    ];
  }
}
