<?php

namespace App\Filament\Resources;

use App\Enums\ScoreObjectType;
use App\Filament\Resources\ScoreObjectResource\Pages;
use App\Models\ScoreObject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScoreObjectResource extends Resource
{
    protected static ?string $model = ScoreObject::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Kompetisi';

    protected static ?string $modelLabel = 'Objek Skor';

    protected static ?string $pluralModelLabel = 'Objek Skor';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('challenge_id')
                    ->label('Tantangan')
                    ->relationship('challenge', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options(collect(ScoreObjectType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()]))
                    ->default('object')
                    ->required(),
                Forms\Components\Select::make('capability_tag_id')
                    ->label('Capability Tag')
                    ->relationship('capabilityTag', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('points_per_unit')
                    ->label('Poin per Unit')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('max_quantity')
                    ->label('Jumlah Maks')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('position')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_bonus')
                    ->label('Bonus'),
                Forms\Components\Toggle::make('requires_evidence')
                    ->label('Wajib Bukti'),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Tantangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (ScoreObjectType $state): string => $state->label())
                    ->color(fn (ScoreObjectType $state): string => $state->isPenalty() ? 'danger' : 'info'),
                Tables\Columns\TextColumn::make('points_per_unit')
                    ->label('Poin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('requires_evidence')
                    ->label('Bukti')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(collect(ScoreObjectType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScoreObjects::route('/'),
            'create' => Pages\CreateScoreObject::route('/create'),
            'edit' => Pages\EditScoreObject::route('/{record}/edit'),
        ];
    }
}
