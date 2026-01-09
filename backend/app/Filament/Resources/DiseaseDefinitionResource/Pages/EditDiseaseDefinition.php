<?php
namespace App\Filament\Resources\DiseaseDefinitionResource\Pages;
use App\Filament\Resources\DiseaseDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditDiseaseDefinition extends EditRecord
{
    protected static string $resource = DiseaseDefinitionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
