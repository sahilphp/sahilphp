<?php

declare(strict_types=1);

namespace Tests\Packaging;

use PHPUnit\Framework\TestCase;

/**
 * Guards files that the installed application reads at runtime.
 *
 * The starter's Composer archive is filtered through .gitattributes, so a
 * broad export-ignore can make source tests pass while a freshly installed app
 * is missing a runtime resource.
 */
final class DistributionArchiveTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_auth_scaffolds_are_not_export_ignored(): void
    {
        $attributes = (string) file_get_contents($this->root.'/.gitattributes');

        self::assertDoesNotMatchRegularExpression(
            '/^\/\.sahil(?:\/scaffolds(?:\/.*)?)?\s+export-ignore$/m',
            $attributes,
            'The installed app needs .sahil/scaffolds for unda:auth; do not exclude it from the archive.',
        );

        $composer = json_decode((string) file_get_contents($this->root.'/composer.json'), true);
        $excluded = $composer['archive']['exclude'] ?? [];

        self::assertIsArray($excluded);
        self::assertNotContains('/.sahil', $excluded, 'The Composer archive must include auth scaffolds.');
        self::assertNotContains('.sahil', $excluded, 'The Composer archive must include auth scaffolds.');
    }

    public function test_auth_scaffolds_are_present_for_both_css_profiles(): void
    {
        $auth = $this->root.'/.sahil/scaffolds/auth';

        foreach (['shared', 'tailwind', 'bootstrap'] as $profile) {
            self::assertDirectoryExists($auth.'/'.$profile, $profile.' auth scaffolds are missing.');
        }

        foreach ([
            'shared/app/Models/User.php',
            'shared/routes/auth.php',
            'tailwind/resources/views/auth/login.sahil.php',
            'bootstrap/resources/views/auth/login.sahil.php',
        ] as $relative) {
            self::assertFileExists($auth.'/'.$relative, $relative.' is missing.');
        }
    }
}
