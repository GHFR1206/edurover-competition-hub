<?php

namespace App\Filament\Resources;

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Kompetisi';

    protected static ?string $modelLabel = 'Kategori';

    protected static ?string $pluralModelLabel = 'Kategori';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kategori')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('level')
                            ->label('Jenjang')
                            ->options(collect(Level::cases())->mapWithKeys(fn ($l) => [$l->value => $l->label()]))
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('persona')
                            ->label('Persona')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(collect(CategoryStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->default('registration')
                            ->required(),
                        Forms\Components\TextInput::make('min_team_members')
                            ->label('Min. Anggota')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('max_team_members')
                            ->label('Maks. Anggota')
                            ->numeric()
                            ->default(3)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Jenjang')
                    ->badge()
                    ->formatStateUsing(fn (Level $state): string => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (CategoryStatus $state): string => match ($state) {
                        CategoryStatus::Registration => 'gray',
                        CategoryStatus::Scheduled => 'info',
                        CategoryStatus::Running => 'warning',
                        CategoryStatus::AllRunsFinished => 'primary',
                        CategoryStatus::Published => 'success',
                    })
                    ->formatStateUsing(fn (CategoryStatus $state): string => $state->label()),
                Tables\Columns\TextColumn::make('teams_count')
                    ->label('Tim')
                    ->counts('teams')
                    ->badge(),
                Tables\Columns\TextColumn::make('challenges_count')
                    ->label('Tantangan')
                    ->counts('challenges')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('level')
                    ->label('Jenjang')
                    ->options(collect(Level::cases())->mapWithKeys(fn ($l) => [$l->value => $l->label()])),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\RulebooksRelationManager::class,
            RelationManagers\ChallengesRelationManager::class,
            RelationManagers\CapabilityTagsRelationManager::class,
        ];
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
