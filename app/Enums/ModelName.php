<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    case GEMINI_2_0_FLASH_LITE = 'gemini-2.0-flash-lite';
    case GEMINI_2_0_FLASH = 'gemini-2.0-flash';

    /**
     * Get all models as an array for frontend consumption
     */
    public static function getAvailableModels(): array
    {
        return array_map(
            fn (ModelName $model): array => $model->toArray(),
            self::cases()
        );
    }

    public function getName(): string
    {
        return match ($this) {
            self::GEMINI_2_0_FLASH => 'Gemini 2.0 Flash',
            self::GEMINI_2_0_FLASH_LITE => 'Gemini 2.0 Flash Lite',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GEMINI_2_0_FLASH => 'Cheapest model, best for smarter tasks',
            self::GEMINI_2_0_FLASH_LITE => 'Cheapest model, best for simpler tasks',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::GEMINI_2_0_FLASH => Provider::Gemini,
            self::GEMINI_2_0_FLASH_LITE => Provider::Gemini,
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'provider' => $this->getProvider()->value,
        ];
    }
}
