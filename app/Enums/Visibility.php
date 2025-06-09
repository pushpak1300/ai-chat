<?php

declare(strict_types=1);

namespace App\Enums;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::PUBLIC->value => 'public',
            self::PRIVATE->value => 'private',
        ];
    }
}
