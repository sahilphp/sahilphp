<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Resources\UserResource;
use Sahil\Http\Exception\NotFoundHttpException;
use Sahil\Http\Request;
use Sahil\Http\Resources\ResourceCollection;
use Sahil\Orm\OrmResult;
use Sahil\Orm\PageResult;

/**
 * Demonstrates native API resources against plain array data — resources
 * work with arrays, objects, or ORM models alike.
 */
class UserApiController
{
    /**
     * Shared demo data source — V2 reuses it; real apps share a repository.
     *
     * @return list<array<string, mixed>>
     */
    protected static function users(): array
    {
        return [
            [
                'id' => 1, 'name' => 'Asha', 'email' => 'asha@example.com',
                'emailVisible' => true, 'joined' => '2024-01-05T10:00:00+00:00',
                'posts' => [['id' => 11, 'title' => 'Karibu'], ['id' => 12, 'title' => 'Mafunzo']],
            ],
            [
                'id' => 2, 'name' => 'Baraka', 'email' => 'baraka@example.com',
                'joined' => '2024-03-14T09:30:00+00:00',
                'posts' => [],
            ],
        ];
    }

    /**
     * GET /api/users — collection with an explicit pagination envelope.
     * The PageResult feeds the existing Paginator for `meta`.
     */
    public function index(Request $request): ResourceCollection
    {
        $users = self::users();
        $page = new PageResult(new OrmResult($users), currentPage: 1, perPage: 10, total: count($users));

        return UserResource::collection($users)->paginate($page);
    }

    /**
     * GET /api/users/{id} — single resource, nested posts, conditional email.
     */
    public function show(Request $request, array $params): UserResource
    {
        foreach (self::users() as $user) {
            if ($user['id'] === (int) ($params['id'] ?? 0)) {
                return UserResource::make($user);
            }
        }

        throw new NotFoundHttpException();
    }
}
