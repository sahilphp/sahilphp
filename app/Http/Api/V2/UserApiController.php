<?php

declare(strict_types=1);

namespace App\Http\Api\V2;

use App\Http\Resources\V2\UserResource;
use App\Http\UserApiController as V1UserApiController;
use Sahil\Http\Exception\NotFoundHttpException;
use Sahil\Http\Request;
use Sahil\Http\Resources\ResourceCollection;
use Sahil\Orm\OrmResult;
use Sahil\Orm\PageResult;

/**
 * V2 user endpoints — same data source as V1, different public
 * representation. Controllers extend the V1 class rather than duplicating
 * data access; the version lives in the resource, not the plumbing.
 */
final class UserApiController extends V1UserApiController
{
    /** GET /api/v2/users — collection with the same pagination envelope. */
    public function index(Request $request): ResourceCollection
    {
        $users = self::users();
        $page = new PageResult(new OrmResult($users), currentPage: 1, perPage: 10, total: count($users));

        return UserResource::collection($users)->paginate($page);
    }

    /** GET /api/v2/users/{id} — the V2 representation of one user. */
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
