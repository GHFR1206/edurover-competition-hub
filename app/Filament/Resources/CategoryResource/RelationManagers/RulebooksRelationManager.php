<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RulebooksRelationManager extends RelationManager
{
    protected static string $relationship = 'rulebooks';

    protected static ?string $title = 'Rulebook (versioned)';

    protected static ?string $modelLabel = 'Rulebook';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->default(fn ($livewire) => ($livewire->getOwnerRecord()->rulebooks()->max('version') ?? 0) + 1)
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('summary')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('aggregation')
                    ->label('Aturan Agregasi (multi-attempt / multi-juri)')
                    ->keyLabel('Parameter')
                    ->valueLabel('Nilai')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('tie_break_order')
                    ->label('Urutan Tie-break')
                    ->simple(
                        Forms\Components\TextInput::make('rule')->label('Aturan'),
                    )
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('penalties')
                    ->label('Penalti')
                    ->schema([
                        Forms\Components\TextInput::make('code')->label('Kode')->required(),
                        Forms\Components\TextInput::make('points')->label('Poin')->numeric()->required(),
                        Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('retry_rules')
                    ->label('Aturan Retry / Re-run')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
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
                    ->label('Dikunci Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
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
