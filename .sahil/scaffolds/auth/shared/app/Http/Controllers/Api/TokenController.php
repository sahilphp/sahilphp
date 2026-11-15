<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Sahil\Auth\AuthManager;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * First-party API-token management + a bearer-protected demo endpoint.
 *
 *   POST   /api/tokens          (session) issue a token — plaintext shown once
 *   GET    /api/tokens          (session) list the caller's tokens
 *   DELETE /api/tokens/{id}     (session) revoke one owned token
 *   GET    /api/user            (bearer)  the token's user as a UserResource
 */
final class TokenController
{
    public function __construct(private readonly AuthManager $auth) {}

    /** Issue a token for the signed-in user. `abilities` accepts a CSV. */
    public function store(Request $request): Response
    {
        $user = $this->auth->user();

        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'string', 'max:1000'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $abilities = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($input['abilities'] ?? '*'))
        )));
        $abilities = $abilities === [] ? ['*'] : $abilities;

        $expires = isset($input['expires_in_days'])
            ? new \DateTimeImmutable('+' . (int) $input['expires_in_days'] . ' days')
            : null;

        $token = $user->createToken($input['name'], $abilities, $expires);

        // The plaintext is returned exactly once — it can never be recovered.
        return Response::json([
            'token' => $token->accessToken->jsonSerialize(),
            'plainTextToken' => $token->plainTextToken,
        ], 201);
    }

    /** The caller's tokens — secrets and hashes are never serialized. */
    public function index(Request $request): Response
    {
        $tokens = [];

        foreach ($this->auth->user()->tokens() as $token) {
            $tokens[] = $token->jsonSerialize();
        }

        return Response::json(['data' => $tokens]);
    }

    /** Owner-scoped revoke — another user's id is a silent no-op 404. */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->auth->user()->revokeToken((string) ($params['id'] ?? ''))) {
            return Response::json(['error' => 'not_found'], 404);
        }

        return Response::empty();
    }

    /**
     * Bearer-authenticated: the token's user. Explicit field list — the
     * user's hidden attributes (password, tokens) never serialize.
     */
    public function me(Request $request): Response
    {
        $user = $this->auth->user();

        return Response::json([
            'id' => $user->getAttribute('id'),
            'name' => $user->getAttribute('name'),
            'email' => $user->getAttribute('email'),
        ]);
    }
}
