<?php

declare(strict_types=1);

namespace App\Frame\Enums;

enum ErrorType: int
{
    case INVALID_SETUP = 0x00000001;
    case UNSUPPORTED_SETUP = 0x00000002;
    case REJECTED_SETUP = 0x00000003;
    case REJECTED_RESUME = 0x00000004;
    case CONNECTION_ERROR = 0x00000101;
    case CONNECTION_CLOSE = 0x00000102;
    case APPLICATION_ERROR = 0x00000201;
    case REJECTED = 0x00000202;
    case CANCELED = 0x00000203;
    case INVALID = 0x00000204;
}
