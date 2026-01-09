<?php
namespace App\Filament\Resources;
use App\Filament\Resources\DiseaseDefinitionResource\Pages;
use App\Filament\Resources\DiseaseDefinitionResource\RelationManagers;
use App\Models\DiseaseDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
class DiseaseDefinitionResource extends Resource
{
    protected static ?string $model = DiseaseDefinition::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('technical_name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Nama Teknis (Folder/Class Name)')
                    ->helperText('Sesuai nama folder dataset. Jangan ubah jika tidak perlu.'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Penyakit (Tampilan User)'),
                Forms\Components\Textarea::make('cause')
                    ->label('Penyebab')
                    ->rows(3),
                Forms\Components\Textarea::make('cure')
                    ->label('Solusi/Pengobatan')
                    ->rows(3),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('technical_name')
                    ->searchable()
                    ->label('Nama Teknis'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nama Tampilan'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Terakhir Update'),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDiseaseDefinitions::route('/'),
            'create' => Pages\CreateDiseaseDefinition::route('/create'),
            'edit' => Pages\EditDiseaseDefinition::route('/{record}/edit'),
        ];
    }
}
