<?php

namespace App\Filament\Resources;

use App\Enums\OperatingSystemType;
use App\Enums\TransferableType;
use App\Enums\TransferStatus;
use App\Filament\Resources\TransferResource\Pages;
use App\Models\Transfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('type')
                    ->options(array_combine(TransferableType::values(), TransferableType::titles()))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('from_server_id')
                    ->relationship('fromServer', 'name')
                    ->label('From Server')
                    ->required(),
                Forms\Components\Select::make('to_server_id')
                    ->relationship('toServer', 'name')
                    ->label('To Server')
                    ->required(),
                Forms\Components\TextInput::make('from_path')
                    ->label('From Path')
                    ->required(),
                Forms\Components\TextInput::make('to_path')
                    ->label('To Path')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_path')->sortable()->limit(80),
                Tables\Columns\TextColumn::make('to_path')->sortable()->limit(80),
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('fromServer.name')->sortable(),
                Tables\Columns\TextColumn::make('toServer.name')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('started_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(TransferStatus::values(), TransferStatus::titles()))
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
            //
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
//            'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
