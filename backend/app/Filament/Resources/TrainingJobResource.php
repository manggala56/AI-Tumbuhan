<?php
namespace App\Filament\Resources;
use App\Filament\Resources\TrainingJobResource\Pages;
use App\Filament\Resources\TrainingJobResource\RelationManagers;
use App\Models\TrainingJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class TrainingJobResource extends Resource
{
    protected static ?string $model = TrainingJob::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Training Jobs';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Training Configuration')
                    ->description('Configure the training job parameters')
                    ->schema([
                        Forms\Components\Select::make('plant_type_id')
                            ->label('Plant Type')
                            ->relationship('plantType', 'name')
                            ->searchable()
                            ->placeholder('Universal Model')
                            ->helperText('Select plant type or leave empty for universal model'),
                        Forms\Components\TextInput::make('learning_rate')
                            ->label('Learning Rate')
                            ->numeric()
                            ->default(0.001)
                            ->step(0.0001)
                            ->helperText('Default: 0.001'),
                        Forms\Components\TextInput::make('epochs')
                            ->label('Epochs')
                            ->numeric()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(200)
                            ->helperText('Number of training iterations (1-200)'),
                        Forms\Components\TextInput::make('batch_size')
                            ->label('Batch Size')
                            ->numeric()
                            ->default(32)
                            ->helperText('Default: 32'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Job Information')
                    ->description('Auto-generated job details')
                    ->schema([
                        Forms\Components\TextInput::make('job_id')
                            ->label('Job ID')
                            ->required()
                            ->default(fn () => 'job_' . time())
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Auto-generated unique identifier'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'running' => 'Running',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('triggered_by')
                            ->label('Triggered By')
                            ->relationship('triggeredBy', 'name')
                            ->default(auth()->id())
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3)
                    ->collapsed(),
                Forms\Components\Section::make('Results')
                    ->description('Training results (filled automatically after completion)')
                    ->schema([
                        Forms\Components\TextInput::make('final_accuracy')
                            ->label('Final Accuracy (%)')
                            ->numeric()
                            ->suffix('%')
                            ->disabled(),
                        Forms\Components\TextInput::make('training_time_seconds')
                            ->label('Training Time')
                            ->numeric()
                            ->suffix('seconds')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Started At')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->disabled(),
                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_id')
                    ->label('Job ID')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('plantType.name')
                    ->label('Plant Type')
                    ->badge()
                    ->color('success')
                    ->placeholder('Universal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_accuracy')
                    ->label('Accuracy')
                    ->badge()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 80 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '-')
                    ->sortable()
                    ->placeholder('Pending'),
                Tables\Columns\TextColumn::make('epochs')
                    ->label('Epochs')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('training_time_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : '-')
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('triggeredBy.name')
                    ->label('Triggered By')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->placeholder('Not started'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('M d, H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not completed'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('plant_type_id')
                    ->label('Plant Type')
                    ->relationship('plantType', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (TrainingJob $record) => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (TrainingJob $record) => in_array($record->status, ['failed', 'completed'])),
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
            'index' => Pages\ListTrainingJobs::route('/'),
            'create' => Pages\CreateTrainingJob::route('/create'),
            'view' => Pages\ViewTrainingJob::route('/{record}'),
            'edit' => Pages\EditTrainingJob::route('/{record}/edit'),
        ];
    }
}
