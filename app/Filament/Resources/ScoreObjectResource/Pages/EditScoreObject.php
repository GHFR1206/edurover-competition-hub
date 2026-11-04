<?php

namespace App\Filament\Resources\ScoreObjectResource\Pages;

use App\Filament\Resources\ScoreObjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScoreObject extends EditRecord
{
    protected static string $resource = ScoreObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
