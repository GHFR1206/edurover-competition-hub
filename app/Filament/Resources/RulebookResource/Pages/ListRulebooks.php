<?php

namespace App\Filament\Resources\RulebookResource\Pages;

use App\Filament\Resources\RulebookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRulebooks extends ListRecords
{
    protected static string $resource = RulebookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
