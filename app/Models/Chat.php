<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ChatFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class Chat extends Model
{
    /** @use HasFactory<ChatFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'visibility',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
