<?php

declare(strict_types=1);

namespace App\Models;

use Sahil\Orm\Model;

/**
 * A registered outbound webhook endpoint. `secret` is hidden — it signs
 * payloads and must never reach an API representation.
 */
final class WebhookEndpoint extends Model
{
    protected ?string $table = 'webhook_endpoints';

    protected array $hidden = ['secret'];

    public function __construct(array $attributes = [], bool $exists = false)
    {
        if ($exists) {
            foreach ($attributes as $key => $value) {
                $this->setRawAttribute((string) $key, $value, false);
            }
            return;
        }
        parent::__construct($attributes);
    }
}
