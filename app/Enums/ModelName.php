<?php

declare(strict_types=1);

namespace App\Enums;

enum ModelName: string
{
    case GEMINI_2_0_FLASH_LITE = 'gemini-2.0-flash-lite';
    case GEMINI_2_0_FLASH = 'gemini-2.0-flash';
}
