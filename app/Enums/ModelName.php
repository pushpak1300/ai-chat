<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    // case GEMINI_2_0_FLASH_LITE = 'gemini-2.0-flash-lite';
    // case GEMINI_2_0_FLASH = 'gemini-2.0-flash';
    case GPT_4O_MINI = 'gpt-4o-mini';
    case GPT_4_1_NANO = 'gpt-4.1-nano';

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
            self::GPT_4O_MINI => 'GPT-4o Mini',
            self::GPT_4_1_NANO => 'GPT-4.1 Nano',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GPT_4O_MINI => 'Cheapest model, best for smarter tasks',
            self::GPT_4_1_NANO => 'Cheapest model, best for simpler tasks',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::GPT_4O_MINI => Provider::OpenAI,
            self::GPT_4_1_NANO => Provider::OpenAI,
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
