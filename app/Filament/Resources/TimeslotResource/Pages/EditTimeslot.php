<?php

namespace App\Filament\Resources\TimeslotResource\Pages;

use App\Filament\Resources\TimeslotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeslot extends EditRecord
{
    protected static string $resource = TimeslotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
