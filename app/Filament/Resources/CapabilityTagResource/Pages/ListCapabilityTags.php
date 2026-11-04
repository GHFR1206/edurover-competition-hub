<?php

namespace App\Filament\Resources\CapabilityTagResource\Pages;

use App\Filament\Resources\CapabilityTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapabilityTags extends ListRecords
{
    protected static string $resource = CapabilityTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
