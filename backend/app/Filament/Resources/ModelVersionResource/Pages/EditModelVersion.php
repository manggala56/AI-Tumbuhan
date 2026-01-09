<?php
namespace App\Filament\Resources\ModelVersionResource\Pages;
use App\Filament\Resources\ModelVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditModelVersion extends EditRecord
{
    protected static string $resource = ModelVersionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
