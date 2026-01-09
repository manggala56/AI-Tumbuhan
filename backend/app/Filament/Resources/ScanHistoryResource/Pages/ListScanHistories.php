<?php
namespace App\Filament\Resources\ScanHistoryResource\Pages;
use App\Filament\Resources\ScanHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListScanHistories extends ListRecords
{
    protected static string $resource = ScanHistoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
