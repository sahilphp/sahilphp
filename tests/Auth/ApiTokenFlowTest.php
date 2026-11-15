<?php

declare(strict_types=1);

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use Sahil\Contracts\Auth\AuthenticatableContract;
use Sahil\Database\ConnectionFactory;
use Sahil\Database\Schema\Schema;
use Sahil\Database\Schema\Table;
use Sahil\Http\Request;
use Sahil\Orm\Model;
use Sahil\Auth\Tokens\HasApiTokens;
use Sahil\Auth\Tokens\PersonalAccessToken;
use Sahil\Auth\Tokens\TokenGuard;

final class StarterTokenUser extends Model implements AuthenticatableContract
{
    use HasApiTokens;

    protected ?string $table = 'users';

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

    public function getAuthIdentifier(): int|string
    {
        return (int) $this->getAttribute('id');
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute('password');
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
}

/**
 * API tokens end-to-end on the booted starter: the scaffold's User
 * template carries HasApiTokens, the tokens migration is scaffolded, and
 * bearer auth + owner-scoped revocation behave correctly.
 */
final class ApiTokenFlowTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite unavailable.');
        }

        $this->path = sys_get_temp_dir() . '/sahil-starter-tok-' . bin2hex(random_bytes(6)) . '.sqlite';
        $db = new ConnectionFactory()->make(['driver' => 'sqlite', 'database' => $this->path]);
        StarterTokenUser::useConnection($db);
        PersonalAccessToken::useConnection($db);

        new Schema($db)->create('users', static function (Table $t): void {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('password');
        });
        new Schema($db)->create('personal_access_tokens', static function (Table $t): void {
            $t->id();
            $t->string('tokenable_type');
            $t->integer('tokenable_id');
            $t->string('name');
            $t->string('token_id', 64)->unique();
            $t->string('token_hash', 64);
            $t->text('abilities');
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        $db->table('users')->insert(['name' => 'Asha', 'email' => 'a@x', 'password' => 'h']);
    }

    protected function tearDown(): void
    {
        StarterTokenUser::forgetConnection();
        PersonalAccessToken::forgetConnection();
        @unlink($this->path);
    }

    public function test_scaffold_ships_token_support(): void
    {
        $root = dirname(__DIR__, 2);
        $user = (string) file_get_contents($root . '/.sahil/scaffolds/auth/shared/app/Models/User.php');
        self::assertStringContainsString('HasApiTokens', $user);
        self::assertFileExists($root . '/.sahil/scaffolds/auth/shared/database/migrations/2026_10_30_000003_create_personal_access_tokens_table.php');
        $routes = (string) file_get_contents($root . '/.sahil/scaffolds/auth/shared/routes/auth.php');
        self::assertStringContainsString("'auth:api'", $routes);
        self::assertStringContainsString('/api/tokens', $routes);
        self::assertFileExists($root . '/.sahil/scaffolds/auth/shared/app/Http/Controllers/Api/TokenController.php');
    }

    public function test_issue_authenticate_and_revoke_cycle(): void
    {
        $user = StarterTokenUser::find(1);
        $new = $user->createToken('mobile', ['posts:read']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{24}\.[0-9a-f]{64}$/', $new->plainTextToken);

        $req = Request::fromGlobals(
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/user',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $new->plainTextToken],
            []
        );

        $authed = new TokenGuard()->authenticate($req);
        self::assertNotNull($authed);
        self::assertSame('Asha', $authed->getAttribute('name'));
        self::assertTrue($authed->tokenCan('posts:read'));

        self::assertCount(1, $user->tokens()->all());
        self::assertTrue($user->revokeToken($new->accessToken->publicId()));
        self::assertNull(new TokenGuard()->authenticate($req));
    }
}
