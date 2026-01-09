<?php
namespace App\Filament\Resources\ScanHistoryResource\Pages;
use App\Filament\Resources\ScanHistoryResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
class ViewScanHistory extends ViewRecord
{
    protected static string $resource = ScanHistoryResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Scan Image')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('')
                            ->size(400)
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\Section::make('Scan Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('plantType.name')
                            ->label('Plant Type')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Scanned By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Scan Date')
                            ->dateTime('F d, Y H:i'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('AI Prediction')
                    ->schema([
                        Infolists\Components\TextEntry::make('ai_result')
                            ->label('Disease Detected')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('ai_confidence')
                            ->label('Confidence Score')
                            ->badge()
                            ->color(fn ($state) => $state >= 85 ? 'success' : ($state >= 70 ? 'warning' : 'danger'))
                            ->formatStateUsing(fn ($state) => number_format($state, 1) . '%'),
                        Infolists\Components\TextEntry::make('ai_model_version')
                            ->label('Model Version'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Treatment Recommendation')
                    ->schema([
                        Infolists\Components\TextEntry::make('treatment_advice')
                            ->label('')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->treatment_advice)),
                Infolists\Components\Section::make('User Feedback')
                    ->schema([
                        Infolists\Components\TextEntry::make('user_rating')
                            ->label('Rating')
                            ->badge()
                            ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                            ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : 'No rating'),
                        Infolists\Components\TextEntry::make('user_comment')
                            ->label('Comment')
                            ->columnSpanFull()
                            ->placeholder('No comment provided'),
                    ])
                    ->visible(fn ($record) => $record->user_rating || $record->user_comment),
                Infolists\Components\Section::make('Researcher Correction')
                    ->schema([
                        Infolists\Components\TextEntry::make('researcher_correction')
                            ->label('Corrected Disease')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('correctedBy.name')
                            ->label('Corrected By'),
                        Infolists\Components\TextEntry::make('corrected_at')
                            ->label('Correction Date')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => !empty($record->researcher_correction)),
                Infolists\Components\Section::make('Shadow Model Results')
                    ->schema([
                        Infolists\Components\TextEntry::make('shadow_result')
                            ->label('Shadow Disease'),
                        Infolists\Components\TextEntry::make('shadow_confidence')
                            ->label('Shadow Confidence')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '-'),
                        Infolists\Components\TextEntry::make('shadow_model_version')
                            ->label('Shadow Model Version'),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->visible(fn ($record) => !empty($record->shadow_result)),
            ]);
    }
}
