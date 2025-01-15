<?php

namespace App\Filament\Resources\TennisCourtResource\Pages;

use App\Filament\Resources\TennisCourtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTennisCourt extends EditRecord
{
    protected static string $resource = TennisCourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
