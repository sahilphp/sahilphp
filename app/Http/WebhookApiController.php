<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Resources\WebhookEndpointResource;
use App\Models\WebhookEndpoint;
use Sahil\Database\Connection;
use Sahil\Http\ApiQuery;
use Sahil\Http\Request;
use Sahil\Http\Resources\ResourceCollection;

/**
 * GET /api/webhook-endpoints — native ApiQuery over a real table:
 *   ?filter[active]=1   ?search=example   ?sort=-created_at
 *   ?page=2&perPage=10  ?fields=id,url
 *
 * Demonstrates allowlisted filters, substring search, allowlisted sorts,
 * a default sort, `uniqueBy('id')` stable ordering (the pk is a string,
 * not an auto-increment), bounded pagination, field selection via the
 * resource's `only()` narrowing, and PageResult → Paginator metadata.
 */
final class WebhookApiController
{
    public function __construct(private readonly Connection $connection)
    {
        WebhookEndpoint::useConnection($connection);
    }

    public function index(Request $request): ResourceCollection
    {
        $query = ApiQuery::from($request)
            ->allowedFilters(['active', 'url' => ['eq', 'like']])
            ->searchable(['url'])
            ->allowedSorts(['url', 'created_at', 'active'])
            ->defaultSort('-created_at')
            ->uniqueBy('id')
            ->allowedFields(['id', 'url', 'events', 'active', 'created_at'])
            ->perPage(15, 100);

        $page = $query->paginate(WebhookEndpoint::query());

        return WebhookEndpointResource::collection($page->result)
            ->only($query->selectedFields())
            ->paginate($page);
    }
}
