<?php

declare(strict_types=1);

namespace App\Http;

use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Exception\NotFoundHttpException;
use Sahil\Http\Request;
use Sahil\Http\Response;
use Sahil\Http\Router;
use Sahil\OpenApi\OpenApi;
use Sahil\Routing\Version;

/**
 * OpenAPI documentation endpoints. Resolved through the kernel's
 * container, so the router injected here is always the one that actually
 * serves the request — no process-global application handle required.
 */
final class DocsController
{
    public function __construct(
        private readonly Router $router,
        private readonly ApplicationContract $application,
    ) {}

    public function spec(): Response
    {
        $this->guard();

        return OpenApi::response($this->router);
    }

    public function ui(): Response
    {
        $this->guard();

        return OpenApi::ui('/docs/openapi.json');
    }

    /** `/docs/{version}/openapi.json` — a spec for one registered version. */
    public function specVersion(Request $request, array $params): Response
    {
        $this->guard();

        return OpenApi::response($this->router, ['api_version' => $this->version($params)]);
    }

    /** `/docs/{version}` — the docs viewer pointed at that version's spec. */
    public function uiVersion(Request $request, array $params): Response
    {
        $this->guard();
        $version = $this->version($params);

        return OpenApi::ui('/docs/' . $version . '/openapi.json');
    }

    /**
     * Only versions actually present in the route table get a document —
     * an unregistered or malformed prefix is a plain 404, never a silent
     * fallback to another version.
     */
    private function version(array $params): string
    {
        try {
            $version = Version::normalize((string) ($params['version'] ?? ''));
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }

        if (!in_array($version, $this->router->versions(), true)) {
            throw new NotFoundHttpException();
        }

        return $version;
    }

    /**
     * `openapi.enabled` gates both endpoints — a 404 keeps disabled docs
     * indistinguishable from routes that never existed.
     */
    private function guard(): void
    {
        if (!$this->application->config()->get('openapi.enabled', true)) {
            throw new NotFoundHttpException();
        }
    }
}
