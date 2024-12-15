<?php

namespace App\Enums;

enum OperatingSystemType: string
{
    use BaseEnumTrait;

    case WINDOWS = 'Windows';
    case LINUX = 'Linux';
    case MAC = 'Mac';
}
