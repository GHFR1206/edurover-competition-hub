<?php

namespace App\Filament\Resources;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Services\RegistrationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $modelLabel = 'Pendaftaran Tim';

    protected static ?string $pluralModelLabel = 'Verifikasi Pendaftaran Tim';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyPermission(['registration.manage', 'registration.verify']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pendaftaran')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('team_id')
                            ->label('Tim')
                            ->relationship('team', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('coach_id')
                            ->label('Coach')
                            ->relationship('coach', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(collect(RegistrationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Persetujuan & Catatan')
                    ->schema([
                        Forms\Components\Toggle::make('consent_parent')
                            ->label('Persetujuan Orang Tua')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Toggle::make('consent_media')
                            ->label('Persetujuan Media')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Panitia')
                            ->rows(2)
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
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Tim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team.school.name')
                    ->label('Sekolah')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.level')
                    ->label('Jenjang')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '—'),
                Tables\Columns\IconColumn::make('consent_parent')
                    ->label('Izin Ortu')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (RegistrationStatus $state): string => match ($state) {
                        RegistrationStatus::Draft => 'gray',
                        RegistrationStatus::Submitted => 'info',
                        RegistrationStatus::UnderReview => 'warning',
                        RegistrationStatus::Verified => 'success',
                        RegistrationStatus::Rejected => 'danger',
                        RegistrationStatus::Withdrawn => 'gray',
                    })
                    ->formatStateUsing(fn (RegistrationStatus $state): string => $state->label()),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Diverifikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Event')
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(RegistrationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('Periksa')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (Registration $record): bool => $record->status === RegistrationStatus::Submitted)
                    ->requiresConfirmation()
                    ->action(function (Registration $record) {
                        app(RegistrationService::class)->startReview($record);
                        Notification::make()->title('Pendaftaran sedang diperiksa.')->success()->send();
                    }),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Registration $record): bool => in_array(
                        $record->status,
                        [RegistrationStatus::Submitted, RegistrationStatus::UnderReview],
                        true,
                    ))
                    ->requiresConfirmation()
                    ->action(function (Registration $record) {
                        $blockers = app(RegistrationService::class)->verify($record, auth()->user());

                        if ($blockers !== []) {
                            Notification::make()
                                ->title('Tidak dapat diverifikasi')
                                ->body(implode(' ', $blockers))
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Pendaftaran terverifikasi.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Registration $record): bool => in_array(
                        $record->status,
                        [RegistrationStatus::Submitted, RegistrationStatus::UnderReview],
                        true,
                    ))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Registration $record, array $data) {
                        app(RegistrationService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Pendaftaran ditolak.')->danger()->send();
                    }),
                Tables\Actions\EditAction::make()->label('Detail'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}
