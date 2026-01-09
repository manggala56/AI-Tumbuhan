<?php
namespace App\Filament\Resources\TrainingJobResource\Pages;
use App\Filament\Resources\TrainingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListTrainingJobs extends ListRecords
{
    protected static string $resource = TrainingJobResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
