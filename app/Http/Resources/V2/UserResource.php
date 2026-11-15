<?php

declare(strict_types=1);

namespace App\Http\Resources\V2;

use App\Http\Resources\UserResource as V1UserResource;

/**
 * V2 user representation — a breaking-shape evolution of V1:
 * `email` is no longer exposed at all, embedded `posts` became a
 * `posts_count` projection, and `name` was renamed `display_name` with a
 * derived `handle`. Fields are still declared explicitly — nothing is
 * enumerated from the underlying data.
 */
final class UserResource extends V1UserResource
{
    public static function apiSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'display_name' => ['type' => 'string'],
                'handle' => ['type' => 'string'],
                'posts_count' => ['type' => 'integer'],
                'joined' => ['type' => 'string', 'format' => 'date-time'],
            ],
            'required' => ['id'],
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->name,
            'handle' => '@' . strtolower((string) $this->name),
            'posts_count' => count($this->posts ?? []),
            'joined' => $this->joined,
        ];
    }
}
