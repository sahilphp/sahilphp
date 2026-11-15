<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Sahil\Http\Resources\Resource;

final class PostResource extends Resource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            // password/token fields on the source never reach the wire.
        ];
    }
}
