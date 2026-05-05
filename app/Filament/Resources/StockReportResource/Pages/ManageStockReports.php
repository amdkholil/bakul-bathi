<?php

namespace App\Filament\Resources\StockReportResource\Pages;

use App\Filament\Resources\StockReportResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStockReports extends ManageRecords
{
    protected static string $resource = StockReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
