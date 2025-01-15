<?php

namespace App\Filament\Resources\TennisCourtResource\Pages;

use App\Filament\Resources\TennisCourtResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTennisCourts extends ListRecords
{
    protected static string $resource = TennisCourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
