<?php

declare(strict_types=1);

namespace Tests\Webhooks;

use PHPUnit\Framework\TestCase;
use Sahil\Contracts\Http\Client\HttpClientContract;
use Sahil\Database\Connection;
use Sahil\Database\Schema\Schema;
use Sahil\Database\Schema\Table;
use Sahil\Foundation\Application;
use Sahil\Http\Client\FakeTransport;
use Sahil\Http\Client\HttpClient;
use Sahil\Webhooks\Jobs\DeliverWebhookJob;
use Sahil\Webhooks\DnsResolverContract;
use Sahil\Webhooks\Webhook;
use Sahil\Webhooks\WebhookDelivery;
use Sahil\Webhooks\WebhookSigner;
use Sahil\Webhooks\WebhookStore;

/**
 * End-to-end on the real app graph: provider-bound store+manager,
 * queue dispatch, signed delivery through a faked transport.
 */
final class WebhookFlowTest extends TestCase
{
    private FakeTransport $http;

    protected function setUp(): void
    {
        $app = $this->boot();
        /** @var Connection $db */
        $db = $app->container()->make(Connection::class);
        $schema = new Schema($db);

        if (!$schema->hasTable('webhook_endpoints')) {
            $schema->create('webhook_endpoints', static function (Table $t): void {
                $t->string('id', 64)->primary();
                $t->string('url');
                $t->text('events');
                $t->integer('active')->default(1);
                $t->text('secret');
                $t->integer('created_at');
                $t->integer('updated_at');
            });
            $schema->create('webhook_deliveries', static function (Table $t): void {
                $t->string('id', 64)->primary();
                $t->string('endpoint_id', 64);
                $t->string('event');
                $t->string('status', 20);
                $t->integer('attempts')->default(0);
                $t->integer('last_status')->nullable();
                $t->text('error')->nullable();
                $t->text('payload');
                $t->integer('created_at');
                $t->integer('completed_at')->nullable();
            });
        }

        $db->table('webhook_deliveries')->where('id', '<>', '')->delete();
        $db->table('webhook_endpoints')->where('id', '<>', '')->delete();

        $this->http = new FakeTransport();
        $app->container()->instance(HttpClientContract::class, new HttpClient($this->http));
        // Deterministic DNS — no real lookups in tests.
        $app->container()->instance(DnsResolverContract::class, new class () implements DnsResolverContract {
            public function resolve(string $host): array
            {
                return ['93.184.216.34'];
            }
        });
        $app->container()->instance(\Sahil\Webhooks\WebhookUrlValidator::class, new \Sahil\Webhooks\WebhookUrlValidator(
            resolver: $app->container()->make(DnsResolverContract::class),
        ));
    }

    protected function tearDown(): void
    {
        Webhook::flushState();
        Application::free();
        parent::tearDown();
    }

    public function test_register_deliver_and_signed_execution_through_the_container(): void
    {
        $endpoint = Webhook::register('https://hooks.example.test/in', ['user.registered'], 'sekrit');

        self::assertArrayNotHasKey('secret', Webhook::endpoints()[0]);
        self::assertSame('sekrit', Webhook::endpoint($endpoint->id)->secret); // round-trips through Encrypter

        $this->http->stub('*', ['status' => 202]);
        [$delivery] = Webhook::deliver('user.registered', ['user_id' => 7]);
        self::assertSame(WebhookDelivery::STATUS_QUEUED, $delivery->status);

        (new DeliverWebhookJob($delivery->id))->handle();

        $requests = $this->http->recorded();
        self::assertCount(1, $requests);
        $req = $requests[0];
        self::assertSame('https://hooks.example.test/in', $req->url);

        $header = static function (string $name) use ($req): ?string {
            foreach ($req->headers as $k => $v) {
                if (strcasecmp($k, $name) === 0) {
                    return $v[0] ?? null;
                }
            }

            return null;
        };

        self::assertTrue((new WebhookSigner())->verify(
            'sekrit',
            (int) $header('X-Sahil-Timestamp'),
            $req->body,
            (string) $header('X-Sahil-Signature'),
        ));

        self::assertSame(WebhookDelivery::STATUS_DELIVERED, Webhook::delivery($delivery->id)->status);
    }

    public function test_delivery_to_nonmatching_event_enqueues_nothing(): void
    {
        Webhook::register('https://hooks.example.test/in', ['user.registered'], 's');
        self::assertSame([], Webhook::deliver('order.placed', ['id' => 1]));
    }

    private function boot(): Application
    {
        require_once dirname(__DIR__, 2).'/bootstrap/app.php';

        $app = \sahil_bootstrap();

        if (Application::peek() !== $app) {
            $configured = new \ReflectionProperty(Application::class, 'configured');
            $configured->setValue(null, $app);
        }

        return $app;
    }
}
