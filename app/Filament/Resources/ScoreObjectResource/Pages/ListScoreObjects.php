<?php

namespace App\Filament\Resources\ScoreObjectResource\Pages;

use App\Filament\Resources\ScoreObjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScoreObjects extends ListRecords
{
    protected static string $resource = ScoreObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
