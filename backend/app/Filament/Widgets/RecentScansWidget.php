<?php
namespace App\Filament\Widgets;
use App\Models\ScanHistory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
class RecentScansWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                ScanHistory::query()
                    ->with(['plantType', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('plantType.name')
                    ->label('Plant Type')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('ai_result')
                    ->label('Disease Detected')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('ai_confidence')
                    ->label('Confidence')
                    ->badge()
                    ->color(fn ($state) => $state >= 85 ? 'success' : ($state >= 70 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%'),
                Tables\Columns\TextColumn::make('user_rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : '-'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Scanned At')
                    ->dateTime('M d, H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (ScanHistory $record): string => route('filament.admin.resources.scan-histories.view', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
