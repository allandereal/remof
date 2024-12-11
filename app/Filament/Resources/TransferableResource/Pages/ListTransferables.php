<?php

namespace App\Filament\Resources\TransferableResource\Pages;

use App\Filament\Resources\TransferableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferables extends ListRecords
{
    protected static string $resource = TransferableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
