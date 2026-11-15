<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Sahil\Http\Resources\Resource;

class UserResource extends Resource
{
    public static function apiSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'email' => ['type' => 'string', 'format' => 'email'],
                'posts' => ['type' => 'array', 'items' => ['type' => 'object']],
                'joined' => ['type' => 'string', 'format' => 'date-time'],],
            'required' => ['id'],
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Conditional exposure — e.g. admins only.
            'email' => $this->when(($this->emailVisible ?? false) === true, $this->email),
            'posts' => PostResource::collection($this->posts ?? []),
            'joined' => $this->joined,
        ];
    }
}
