<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section as FormSection;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Content')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('The slug is used for the URL. Leave empty to auto-generate from title.'),

                                Forms\Components\Textarea::make('excerpt')
                                    ->rows(3)
                                    ->helperText('A brief summary of the blog post. This will be shown in blog listings.')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('content')
                                    ->required()
                                    ->fileAttachmentsDirectory('blog-attachments')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Featured Image')
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image')
                                    ->image()
                                    ->directory('blog-images')
                                    ->maxSize(2048)
                                    ->helperText('Recommended size: 1200x800px, max 2MB')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Settings')
                            ->schema([
                                Forms\Components\TextInput::make('author')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('Admin'),

                                Forms\Components\TextInput::make('category')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('Uncategorized'),

                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                    ])
                                    ->default('draft')
                                    ->live(),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->helperText('Set a future date to schedule the post')
                                    ->visible(fn(Forms\Get $get) => $get('status') === 'published' || $get('status') === 'scheduled')
                                    ->required(fn(Forms\Get $get) => $get('status') === 'scheduled'),

                                Forms\Components\TagsInput::make('tags')
                                    ->separator(',')
                                    ->helperText('Add relevant tags separated by commas'),

                                Forms\Components\TextInput::make('views')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->helperText('View count is automatically updated'),
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
                Tables\Columns\ImageColumn::make('featured_image')
                    ->square()
                    ->size(50)
                    ->label('Image'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->description(fn(Blog $record): string => Str::limit($record->excerpt ?? '', 60)),

                Tables\Columns\TextColumn::make('author')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'primary' => 'scheduled',
                    ]),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->options(function () {
                        return \App\Models\Blog::select('category')->distinct()->pluck('category', 'category');
                    }),

                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from'),
                        Forms\Components\DatePicker::make('published_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
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
                        Tables\Actions\BulkAction::make('publish')
                            ->label('Publish Selected')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->action(fn(Collection $records) => $records->each->update(['status' => 'published', 'published_at' => now()])),

                        Tables\Actions\BulkAction::make('draft')
                            ->label('Set as Draft')
                            ->icon('heroicon-o-document')
                            ->color('warning')
                            ->action(fn(Collection $records) => $records->each->update(['status' => 'draft'])),
                    ]),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
