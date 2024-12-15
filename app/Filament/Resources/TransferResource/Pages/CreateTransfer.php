<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Enums\OperatingSystemType;
use App\Enums\TransferableType;
use App\Filament\Resources\TransferResource;
use App\Models\Server;
use Filament\Resources\Pages\CreateRecord;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $toServer = Server::find($data['to_server_id']);

        if ($data['type'] === TransferableType::DIRECTORY->value){
            $data['to_path'] .= (str_ends_with($data['to_path'], '/') ? '' : '/') . preg_replace("/^.*\/(.*)$/", "$1", $data['from_path']);
        }

        if ($toServer->os === OperatingSystemType::WINDOWS->value && !str_starts_with($data['to_path'], '/')){
            $data['to_path'] = '/'.$data['to_path'];
        }

        return $data;
    }
}
