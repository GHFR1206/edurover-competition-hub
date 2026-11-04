<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\CategoryStatus;
use App\Enums\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Kategori (per Jenjang)';

    protected static ?string $modelLabel = 'Kategori';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('level')
                    ->label('Jenjang')
                    ->options(collect(Level::cases())->mapWithKeys(fn ($l) => [$l->value => $l->label()]))
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, $livewire) => $rule->where('event_id', $livewire->getOwnerRecord()->id)),
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
                    ->label('Min. Anggota Tim')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('max_team_members')
                    ->label('Maks. Anggota Tim')
                    ->numeric()
                    ->default(3)
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('level')
                    ->label('Jenjang')
                    ->badge()
                    ->formatStateUsing(fn (Level $state): string => $state->label()),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona')
                    ->label('Persona')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->label()),
                Tables\Columns\TextColumn::make('teams_count')
                    ->label('Tim')
                    ->counts('teams')
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
