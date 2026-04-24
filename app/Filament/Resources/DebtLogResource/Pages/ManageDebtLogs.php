<?php

namespace App\Filament\Resources\DebtLogResource\Pages;

use App\Filament\Resources\DebtLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDebtLogs extends ManageRecords
{
    protected static string $resource = DebtLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
