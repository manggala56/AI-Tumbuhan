<?php
namespace App\Filament\Resources\ScanHistoryResource\Pages;
use App\Filament\Resources\ScanHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditScanHistory extends EditRecord
{
    protected static string $resource = ScanHistoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
