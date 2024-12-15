<?php

namespace App\Enums;

enum TransferableType: string
{
    use BaseEnumTrait;

    case DIRECTORY = 'Directory';
    case FILE = 'File';
}
