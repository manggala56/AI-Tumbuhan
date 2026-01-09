<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ModelVersionResource\Pages;
use App\Filament\Resources\ModelVersionResource\RelationManagers;
use App\Models\ModelVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class ModelVersionResource extends Resource
{
    protected static ?string $model = ModelVersion::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'AI Models';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Model Information')
                    ->schema([
                        Forms\Components\Select::make('plant_type_id')
                            ->label('Plant Type')
                            ->relationship('plantType', 'name')
                            ->searchable()
                            ->placeholder('Universal Model'),
                        Forms\Components\TextInput::make('version_name')
                            ->label('Version Name')
                            ->required()
                            ->placeholder('e.g., v1.2.0'),
                        Forms\Components\TextInput::make('file_path')
                            ->label('Model File Path')
                            ->required()
                            ->placeholder('/models/tomato_v1.h5'),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Performance Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('accuracy')
                            ->label('Accuracy (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('precision_score')
                            ->label('Precision (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('recall_score')
                            ->label('Recall (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('f1_score')
                            ->label('F1 Score (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(4)
                    ->collapsible(),
                Forms\Components\Section::make('Training Details')
                    ->schema([
                        Forms\Components\DateTimePicker::make('trained_at')
                            ->label('Training Date'),
                        Forms\Components\TextInput::make('training_samples')
                            ->label('Training Samples')
                            ->numeric()
                            ->suffix('images'),
                        Forms\Components\TextInput::make('epochs')
                            ->label('Epochs')
                            ->numeric(),
                        Forms\Components\TextInput::make('learning_rate')
                            ->label('Learning Rate')
                            ->numeric()
                            ->step(0.00000001),
                    ])
                    ->columns(4)
                    ->collapsible(),
                Forms\Components\Section::make('Deployment')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active (Production)')
                            ->helperText('Only one model can be active per plant type'),
                        Forms\Components\Toggle::make('is_shadow')
                            ->label('Shadow Model (A/B Testing)')
                            ->helperText('Shadow model runs alongside production for comparison'),
                        Forms\Components\DateTimePicker::make('deployed_at')
                            ->label('Deployment Date'),
                        Forms\Components\Select::make('deployed_by')
                            ->label('Deployed By')
                            ->relationship('deployedBy', 'name')
                            ->searchable(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version_name')
                    ->label('Version')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('plantType.name')
                    ->label('Plant Type')
                    ->badge()
                    ->color('success')
                    ->placeholder('Universal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('accuracy')
                    ->label('Accuracy')
                    ->badge()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 80 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('f1_score')
                    ->label('F1 Score')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1) . '%' : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_shadow')
                    ->label('Shadow')
                    ->boolean()
                    ->trueIcon('heroicon-o-beaker')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('training_samples')
                    ->label('Samples')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trained_at')
                    ->label('Trained')
                    ->dateTime('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deployedBy.name')
                    ->label('Deployed By')
                    ->toggleable()
                    ->placeholder('Not deployed'),
            ])
            ->defaultSort('trained_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('plant_type_id')
                    ->label('Plant Type')
                    ->relationship('plantType', 'name')
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Models'),
                Tables\Filters\TernaryFilter::make('is_shadow')
                    ->label('Shadow Models'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListModelVersions::route('/'),
            'create' => Pages\CreateModelVersion::route('/create'),
            'edit' => Pages\EditModelVersion::route('/{record}/edit'),
        ];
    }
}
