<?php

namespace App\Filament\Resources\CapabilityTagResource\Pages;

use App\Filament\Resources\CapabilityTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCapabilityTag extends EditRecord
{
    protected static string $resource = CapabilityTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
