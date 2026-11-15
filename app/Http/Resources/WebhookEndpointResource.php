<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Sahil\Http\Resources\Resource;

final class WebhookEndpointResource extends Resource
{
    public static function apiSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'string'],
                'url' => ['type' => 'string', 'format' => 'uri'],
                'events' => ['type' => 'string'],
                'active' => ['type' => 'boolean'],
                'created_at' => ['type' => 'integer'],],
            'required' => ['id'],
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'events' => $this->events,
            'active' => (bool) $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
