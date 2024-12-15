<?php

namespace App\Enums;

enum TransferStatus: string
{
    use BaseEnumTrait;

    case PENDING = 'pending';
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
