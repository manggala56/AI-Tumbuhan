<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ScanHistoryResource\Pages;
use App\Filament\Resources\ScanHistoryResource\RelationManagers;
use App\Models\ScanHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class ScanHistoryResource extends Resource
{
    protected static ?string $model = ScanHistory::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Scan History';
    protected static ?string $modelLabel = 'Scan';
    protected static ?string $pluralModelLabel = 'Scans';
    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Scan Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('plant_type_id')
                            ->label('Plant Type')
                            ->relationship('plantType', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Scan Image')
                            ->image()
                            ->required()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('AI Prediction Results')
                    ->schema([
                        Forms\Components\TextInput::make('ai_result')
                            ->label('Disease Detected')
                            ->placeholder('e.g., Early Blight'),
                        Forms\Components\TextInput::make('ai_confidence')
                            ->label('Confidence Score (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('ai_model_version')
                            ->label('Model Version'),
                        Forms\Components\Textarea::make('treatment_advice')
                            ->label('Treatment Recommendation')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),
                Forms\Components\Section::make('Shadow Model (A/B Testing)')
                    ->schema([
                        Forms\Components\TextInput::make('shadow_result')
                            ->label('Shadow Disease Result'),
                        Forms\Components\TextInput::make('shadow_confidence')
                            ->label('Shadow Confidence (%)')
                            ->numeric()
                            ->suffix('%'),
                        Forms\Components\TextInput::make('shadow_model_version')
                            ->label('Shadow Model Version'),
                    ])
                    ->columns(3)
                    ->collapsed(),
                Forms\Components\Section::make('User Feedback')
                    ->schema([
                        Forms\Components\Select::make('user_rating')
                            ->label('Rating')
                            ->options([
                                1 => '⭐ 1 Star',
                                2 => '⭐⭐ 2 Stars',
                                3 => '⭐⭐⭐ 3 Stars',
                                4 => '⭐⭐⭐⭐ 4 Stars',
                                5 => '⭐⭐⭐⭐⭐ 5 Stars',
                            ]),
                        Forms\Components\Textarea::make('user_comment')
                            ->label('User Comment')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Researcher Correction')
                    ->schema([
                        Forms\Components\TextInput::make('researcher_correction')
                            ->label('Corrected Disease Name')
                            ->placeholder('If AI was wrong, enter correct disease'),
                        Forms\Components\Select::make('corrected_by')
                            ->label('Corrected By')
                            ->relationship('correctedBy', 'name')
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('corrected_at')
                            ->label('Correction Date'),
                    ])
                    ->columns(3)
                    ->collapsible(),
                Forms\Components\Section::make('Training Dataset')
                    ->schema([
                        Forms\Components\Toggle::make('is_training_ready')
                            ->label('Ready for Training')
                            ->helperText('Mark this scan as ready to be used in model training'),
                        Forms\Components\Select::make('approved_by')
                            ->label('Approved By')
                            ->relationship('approvedBy', 'name')
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('approved_for_training_at')
                            ->label('Approval Date'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular()
                    ->size(60),
                Tables\Columns\TextColumn::make('plantType.name')
                    ->label('Plant Type')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai_result')
                    ->label('Disease Detected')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('ai_confidence')
                    ->label('Confidence')
                    ->badge()
                    ->color(fn ($state) => $state >= 85 ? 'success' : ($state >= 70 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('⭐', $state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_training_ready')
                    ->label('Training Ready')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('researcher_correction')
                    ->label('Corrected')
                    ->badge()
                    ->color('warning')
                    ->toggleable()
                    ->placeholder('No correction'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Scanned At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('plant_type_id')
                    ->label('Plant Type')
                    ->relationship('plantType', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('low_confidence')
                    ->label('Low Confidence (<70%)')
                    ->query(fn ($query) => $query->where('ai_confidence', '<', 70)),
                Tables\Filters\Filter::make('needs_review')
                    ->label('Needs Review')
                    ->query(fn ($query) => $query->where('ai_confidence', '<', 80)->whereNull('researcher_correction')),
                Tables\Filters\SelectFilter::make('user_rating')
                    ->label('User Rating')
                    ->options([
                        1 => '⭐ 1 Star',
                        2 => '⭐⭐ 2 Stars',
                        3 => '⭐⭐⭐ 3 Stars',
                        4 => '⭐⭐⭐⭐ 4 Stars',
                        5 => '⭐⭐⭐⭐⭐ 5 Stars',
                    ]),
                Tables\Filters\TernaryFilter::make('is_training_ready')
                    ->label('Training Ready'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('validate')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn () => auth()->user()->can('scan.correct'))
                    ->form([
                        Forms\Components\Radio::make('is_correct')
                            ->label('Apakah prediksi AI benar?')
                            ->options([
                                'yes' => 'Ya, Benar',
                                'no' => 'Tidak, Salah'
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('correction')
                            ->label('Pilih Penyakit yang Benar')
                            ->options(\App\Models\DiseaseDefinition::pluck('name', 'name'))
                            ->searchable()
                            ->required(fn (Forms\Get $get) => $get('is_correct') === 'no')
                            ->visible(fn (Forms\Get $get) => $get('is_correct') === 'no'),
                        Forms\Components\Textarea::make('validation_note')
                            ->label('Catatan Validasi')
                            ->rows(2),
                    ])
                    ->action(function (ScanHistory $record, array $data) {
                        $correctDisease = $data['is_correct'] === 'yes' ? $record->ai_result : $data['correction'];
                        $record->update([
                            'researcher_correction' => $correctDisease,
                            'corrected_by' => auth()->id(),
                            'corrected_at' => now(),
                            'is_training_ready' => true,
                            'approved_by' => auth()->id(),
                            'approved_for_training_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Scan Validated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScanHistories::route('/'),
            'create' => Pages\CreateScanHistory::route('/create'),
            'view' => Pages\ViewScanHistory::route('/{record}'),
            'edit' => Pages\EditScanHistory::route('/{record}/edit'),
        ];
    }
}
