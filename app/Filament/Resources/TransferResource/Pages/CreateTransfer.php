<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Filament\Resources\TransferResource;
use App\Models\Transferable;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $transferable = Transferable::find($data['transferable_id']);

        if ($transferable->isDirectory()){
            $data['path'] .= (str_ends_with($data['path'], '/') ? '' : '/') . $transferable->getLastPathPart();
        }

        return $data;
    }
}
