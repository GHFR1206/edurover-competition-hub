<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ChallengesRelationManager extends RelationManager
{
    protected static string $relationship = 'challenges';

    protected static ?string $title = 'Tantangan Dinamis';

    protected static ?string $modelLabel = 'Tantangan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Tantangan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('theme')
                    ->label('Tema')
                    ->maxLength(255),
                Forms\Components\TextInput::make('position')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('mission_brief')
                    ->label('Brief Misi')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme')
                    ->label('Tema')
                    ->badge(),
                Tables\Columns\TextColumn::make('score_objects_count')
                    ->label('Objek Skor')
                    ->counts('scoreObjects')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
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
