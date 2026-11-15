<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use Sahil\Foundation\Application;
use Sahil\Http\Request;
use Sahil\Http\UploadedFile;
use Sahil\Validation\Exists;
use Sahil\Validation\ValidationException;
use Sahil\Validation\Validator;

/**
 * Validation against the real booted application: the public API, the
 * localized message catalog (Swahili by default), request integration
 * and file rules end to end.
 */
final class ValidationFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        Application::free();
        parent::tearDown();
    }

    public function test_make_validates_with_the_public_api(): void
    {
        $this->boot();

        $result = Validator::make(
            ['email' => 'nope', 'age' => '30', 'role' => 'admin'],
            ['email' => 'required|email', 'age' => 'integer|min:18', 'role' => 'in:admin,user'],
        );

        self::assertTrue($result->fails());
        self::assertNotNull($result->first());
        self::assertSame(['age' => 30, 'role' => 'admin'], $result->validated());
    }

    public function test_default_messages_follow_the_application_locale(): void
    {
        $this->boot();

        $result = Validator::make(['email' => ''], ['email' => 'required|email']);
        $message = $result->first();

        // The booted app localizes validation messages (Swahili default).
        self::assertNotSame('This field is required.', $message);
        self::assertStringContainsString('email', (string) $message);
    }

    public function test_request_validate_throws_the_framework_exception(): void
    {
        $this->boot();

        $request = Request::fromGlobals(
            server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/x', 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            form: ['email' => 'bad'],
        );

        try {
            $request->validate(['email' => 'required|email']);
            self::fail('expected ValidationException');
        } catch (ValidationException $failure) {
            self::assertArrayHasKey('email', $failure->result()->errors());
        }
    }

    public function test_file_rules_run_against_real_uploaded_files(): void
    {
        $this->boot();

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'flow');
        file_put_contents($tmp, $png);

        try {
            $request = Request::fromGlobals(
                server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/avatar', 'CONTENT_TYPE' => 'multipart/form-data'],
                files: ['avatar' => ['tmp_name' => $tmp, 'name' => 'a.png', 'type' => 'image/png', 'error' => 0, 'size' => strlen($png)]],
            );

            $data = $request->validate(['avatar' => 'required|file|image']);
            self::assertInstanceOf(UploadedFile::class, $data['avatar']);
        } finally {
            @unlink($tmp);
        }
    }

    public function test_custom_rule_objects_compose_with_builtins(): void
    {
        $this->boot();

        $result = Validator::make(
            ['code' => 'wrong'],
            ['code' => ['required', new class implements \Sahil\Validation\RuleContract {
                public function validate(string $field, mixed $value): ?string
                {
                    return $value === 'sekret' ? null : 'The code is not valid.';
                }
            }]],
        );

        self::assertTrue($result->fails());
        self::assertSame('The code is not valid.', $result->first());
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
