<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RulebookResource\Pages;
use App\Models\Rulebook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RulebookResource extends Resource
{
    protected static ?string $model = Rulebook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Kompetisi';

    protected static ?string $modelLabel = 'Rulebook';

    protected static ?string $pluralModelLabel = 'Rulebook';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draft', 'locked' => 'Terkunci'])
                    ->default('draft')
                    ->required(),
                Forms\Components\Textarea::make('summary')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('aggregation')
                    ->label('Agregasi')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('retry_rules')
                    ->label('Aturan Retry')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'locked' ? 'danger' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'locked' ? 'Terkunci' : 'Draft'),
                Tables\Columns\TextColumn::make('locked_at')
                    ->label('Dikunci')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRulebooks::route('/'),
            'create' => Pages\CreateRulebook::route('/create'),
            'edit' => Pages\EditRulebook::route('/{record}/edit'),
        ];
    }
}
