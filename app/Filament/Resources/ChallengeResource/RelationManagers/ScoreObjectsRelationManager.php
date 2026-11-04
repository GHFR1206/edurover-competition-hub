<?php

namespace App\Filament\Resources\ChallengeResource\RelationManagers;

use App\Enums\ScoreObjectType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ScoreObjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'scoreObjects';

    protected static ?string $title = 'Objek Skor (dinamis)';

    protected static ?string $modelLabel = 'Objek Skor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Objek Penilaian')
                    ->schema([
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
                            ->default(0)
                            ->helperText('Boleh negatif untuk penalti. Tidak ada batas maksimal total skor.'),
                        Forms\Components\TextInput::make('max_quantity')
                            ->label('Jumlah Maks (opsional)')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\Toggle::make('is_bonus')
                            ->label('Bonus'),
                        Forms\Components\Toggle::make('requires_evidence')
                            ->label('Wajib Bukti (evidence)'),
                        Forms\Components\TextInput::make('position')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label('#')
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
                    ->label('Poin/Unit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_quantity')
                    ->label('Maks')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('requires_evidence')
                    ->label('Bukti')
                    ->boolean(),
            ])
            ->reorderable('position')
            ->defaultSort('position')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
