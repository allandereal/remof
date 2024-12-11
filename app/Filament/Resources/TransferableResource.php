<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferableResource\Pages;
use App\Filament\Resources\TransferableResource\RelationManagers;
use App\Models\Transferable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class TransferableResource extends Resource
{
    protected static ?string $model = Transferable::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\Textarea::make('hash')->readOnly()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('size')
                    ->numeric(),
                Forms\Components\TextInput::make('path')
                    ->required()
                    ->live(true)
                    ->afterStateUpdated(function (?string $state, Forms\Get $get, Forms\Set $set) {
                        $set(
                            'hash',
                            Process::run('b2sum "'.$state.'" | awk \'{ print $1 }\'')->output()
                        );

                        $set('size', filesize($state));
                    }),
                Forms\Components\TextInput::make('transferable_id')
                    ->nullable()
                    ->numeric(),
                Forms\Components\Select::make('server_id')
                    ->relationship('server', 'name')
                    ->required(),
                Forms\Components\Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('size')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transferable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('server.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferables::route('/'),
            'create' => Pages\CreateTransferable::route('/create'),
            'edit' => Pages\EditTransferable::route('/{record}/edit'),
        ];
    }
}
