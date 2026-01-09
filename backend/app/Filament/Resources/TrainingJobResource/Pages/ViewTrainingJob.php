<?php
namespace App\Filament\Resources\TrainingJobResource\Pages;
use App\Filament\Resources\TrainingJobResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
class ViewTrainingJob extends ViewRecord
{
    protected static string $resource = TrainingJobResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('job_id')
                            ->label('Job ID')
                            ->copyable()
                            ->badge()
                            ->color('gray'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'running' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'pending' => 'heroicon-o-clock',
                                'running' => 'heroicon-o-arrow-path',
                                'completed' => 'heroicon-o-check-circle',
                                'failed' => 'heroicon-o-x-circle',
                            }),
                        Infolists\Components\TextEntry::make('plantType.name')
                            ->label('Plant Type')
                            ->badge()
                            ->color('success')
                            ->placeholder('Universal Model'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Training Parameters')
                    ->schema([
                        Infolists\Components\TextEntry::make('learning_rate')
                            ->label('Learning Rate'),
                        Infolists\Components\TextEntry::make('epochs')
                            ->label('Epochs'),
                        Infolists\Components\TextEntry::make('batch_size')
                            ->label('Batch Size'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Results')
                    ->schema([
                        Infolists\Components\TextEntry::make('final_accuracy')
                            ->label('Final Accuracy')
                            ->badge()
                            ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 80 ? 'warning' : 'danger'))
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . '%' : 'Not completed')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('training_time_seconds')
                            ->label('Training Duration')
                            ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : 'N/A'),
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime('F d, Y H:i:s')
                            ->placeholder('Not started'),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime('F d, Y H:i:s')
                            ->placeholder('Not completed'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->status !== 'pending'),
                Infolists\Components\Section::make('Error Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('')
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->error_message)),
                Infolists\Components\Section::make('Job Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('triggeredBy.name')
                            ->label('Triggered By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('F d, Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
