<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * `php sahil ui:setup` — install exactly one UI kit and one CSS framework.
 *
 * The base application ships no UI kit code. This command copies only the
 * chosen stub trees from `stubs/ui-kits/<kit>` and `stubs/css/<css>`, writes
 * a `package.json` and `vite.config.js` containing only those dependencies,
 * then deletes the whole `stubs/` directory.
 *
 * Choices are resolved in this order:
 *   1. explicit `--ui-kit` / `--css` flags
 *   2. SAHILPHP_UI_KIT / SAHILPHP_CSS environment variables (set by the installer)
 *   3. an interactive question
 *   4. the non-interactive defaults: none + tailwind
 *
 * The class is deliberately free of framework dependencies so the installer
 * can run it before `composer install`.
 */
final class UiSetup
{
    /** @var list<string> */
    private const UI_KITS = ['firefiber', 'react', 'vue', 'svelte', 'none'];

    /** @var list<string> */
    private const CSS_FRAMEWORKS = ['tailwind', 'bootstrap'];

    /** @var array<string, string> */
    private const UI_KIT_LABELS = [
        'firefiber' => 'Firefiber  (server-driven reactive UI, like Livewire)',
        'react' => 'React      (React + Vite)',
        'vue' => 'Vue        (Vue 3 + Vite)',
        'svelte' => 'Svelte     (Svelte + Vite)',
        'none' => 'None       (sahil template engine, no JS framework)',
    ];

    /** @var array<string, string> */
    private const CSS_LABELS = [
        'tailwind' => 'Tailwind CSS v4',
        'bootstrap' => 'Bootstrap 5',
    ];

    /** @var list<string> */
    private array $written = [];

    private string $root = '';

    /**
     * @param list<string> $args arguments after the command name
     */
    public function run(array $args, string $basePath): int
    {
        try {
            $options = $this->parseOptions($args);
            $root = $options['path'] ?? $basePath;
            $root = rtrim($root, '/');
            $this->root = $root;

            if ($root === '' || !is_dir($root)) {
                throw new RuntimeException(sprintf('project directory [%s] does not exist.', $root));
            }

            $interactive = !$options['no_interaction'] && $this->isInteractive();
            $uiKit = $this->resolve(
                'ui-kit',
                $options['ui_kit'],
                getenv('SAHILPHP_UI_KIT') ?: null,
                self::UI_KITS,
                self::UI_KIT_LABELS,
                'UI kit',
                $interactive,
                'none',
            );
            $css = $this->resolve(
                'css',
                $options['css'],
                getenv('SAHILPHP_CSS') ?: null,
                self::CSS_FRAMEWORKS,
                self::CSS_LABELS,
                'CSS framework',
                $interactive,
                'tailwind',
            );

            $this->install($root, $uiKit, $css);

            fwrite(STDOUT, sprintf("UI setup complete: %s + %s.\n", $uiKit, $css));
            fwrite(STDOUT, "Files written:\n");

            foreach (array_unique($this->written) as $relative) {
                fwrite(STDOUT, sprintf("  %s\n", $relative));
            }

            fwrite(STDOUT, "\nNext steps:\n");
            fwrite(STDOUT, "  npm install\n");
            fwrite(STDOUT, "  npm run build\n");
            fwrite(STDOUT, "  php sahil start\n");

            return 0;
        } catch (RuntimeException $failure) {
            fwrite(STDERR, sprintf("ui:setup: %s\n", $failure->getMessage()));

            return 1;
        }
    }

    /**
     * @param list<string> $args
     *
     * @return array{ui_kit: ?string, css: ?string, path: ?string, no_interaction: bool}
     */
    private function parseOptions(array $args): array
    {
        $options = [
            'ui_kit' => null,
            'css' => null,
            'path' => null,
            'no_interaction' => false,
        ];

        foreach ($args as $argument) {
            if ($argument === '--no-interaction' || $argument === '-n') {
                $options['no_interaction'] = true;

                continue;
            }

            if (str_starts_with($argument, '--ui-kit=')) {
                $options['ui_kit'] = substr($argument, 9);

                continue;
            }

            if (str_starts_with($argument, '--css=')) {
                $options['css'] = substr($argument, 6);

                continue;
            }

            if (str_starts_with($argument, '--path=')) {
                $options['path'] = substr($argument, 7);

                continue;
            }

            throw new RuntimeException(sprintf(
                'unknown option [%s]. usage: php sahil ui:setup [--ui-kit=firefiber|react|vue|svelte|none] [--css=tailwind|bootstrap] [--no-interaction] [--path=DIR]',
                $argument,
            ));
        }

        return $options;
    }

    /**
     * @param list<string> $allowed
     * @param array<string, string> $labels
     */
    private function resolve(
        string $flag,
        ?string $option,
        ?string $environment,
        array $allowed,
        array $labels,
        string $label,
        bool $interactive,
        string $default,
    ): string {
        if ($option !== null) {
            return $this->validate($option, $allowed, $labels, $flag);
        }

        if ($environment !== null && $environment !== '') {
            return $this->validate($environment, $allowed, $labels, $flag, 'SAHILPHP_'.strtoupper(str_replace('-', '_', $flag)));
        }

        if ($interactive) {
            return $this->ask($label, $allowed, $labels);
        }

        fwrite(STDOUT, sprintf("ui:setup: no %s selected; using [%s].\n", strtolower($label), $default));

        return $default;
    }

    /**
     * @param list<string> $allowed
     * @param array<string, string> $labels
     */
    private function validate(
        string $value,
        array $allowed,
        array $labels,
        string $flag,
        ?string $source = null,
    ): string {
        $normalised = strtolower(trim($value));

        $aliases = [
            'tailwind css v4' => 'tailwind',
            'tailwindcss' => 'tailwind',
            'bootstrap 5' => 'bootstrap',
            'bootstrap5' => 'bootstrap',
        ];
        $normalised = $aliases[$normalised] ?? $normalised;

        if (!in_array($normalised, $allowed, true)) {
            throw new RuntimeException(sprintf(
                'invalid %s [%s]%s. Valid values: %s.',
                $flag,
                $value,
                $source === null ? '' : sprintf(' (from %s)', $source),
                implode(', ', $allowed),
            ));
        }

        return $normalised;
    }

    /**
     * @param list<string> $allowed
     * @param array<string, string> $labels
     */
    private function ask(string $label, array $allowed, array $labels): string
    {
        $choices = [];

        foreach ($allowed as $index => $value) {
            $choices[] = $labels[$value] ?? $value;
        }

        while (true) {
            fwrite(STDOUT, sprintf("Choose your %s:\n", $label));

            foreach ($choices as $index => $text) {
                fwrite(STDOUT, sprintf("  %d. %s\n", $index + 1, $text));
            }

            fwrite(STDOUT, '> ');
            $line = fgets(STDIN);

            if ($line === false) {
                throw new RuntimeException(sprintf('unable to read the %s choice from STDIN.', strtolower($label)));
            }

            $answer = trim($line);

            if ($answer === '') {
                fwrite(STDOUT, "Enter a number or a name.\n");

                continue;
            }

            if (ctype_digit($answer)) {
                $index = (int) $answer - 1;

                if (isset($allowed[$index])) {
                    return $allowed[$index];
                }

                fwrite(STDOUT, sprintf("Invalid choice [%s]. Enter a number between 1 and %d.\n", $answer, count($allowed)));

                continue;
            }

            $normalised = strtolower($answer);

            foreach ($allowed as $value) {
                if ($normalised === $value || str_starts_with(strtolower($labels[$value] ?? ''), $normalised)) {
                    return $value;
                }
            }

            fwrite(STDOUT, sprintf("Invalid choice [%s]. Valid values: %s.\n", $answer, implode(', ', $allowed)));
        }
    }

    private function install(string $root, string $uiKit, string $css): void
    {
        $stubRoot = $root.'/stubs';
        $kitRoot = $stubRoot.'/ui-kits/'.$uiKit;
        $cssRoot = $stubRoot.'/css/'.$css;

        foreach ([$kitRoot, $cssRoot] as $required) {
            if (!is_dir($required)) {
                throw new RuntimeException(sprintf(
                    'missing stub directory [%s]; is this a SahilPHP starter-kit with stubs/ present?',
                    $required,
                ));
            }
        }

        // Copy the UI kit first, then the CSS framework. The CSS copy owns the
        // views and stylesheet; the UI kit copy owns the JavaScript entry.
        $this->copyTree($kitRoot, $root, ['package.json', 'vite.config.js']);
        $this->copyTree($cssRoot, $root, ['package.json', 'vite.config.js', 'resources/js']);

        if ($uiKit === 'none') {
            $this->copyTree($cssRoot.'/resources/js', $root.'/resources/js');
        }

        $package = $this->mergePackage($kitRoot.'/package.json', $cssRoot.'/package.json');
        $this->write($root.'/package.json', $this->encodeJson($package));
        $this->write($root.'/vite.config.js', $this->viteConfig($uiKit, $css));
        $this->write($root.'/.sahil-ui.json', $this->encodeJson([
            'profile' => $css,
            'ui_kit' => $uiKit,
        ]));

        $this->configureProviders($root, $uiKit);

        $this->removeTree($stubRoot);
    }

    /**
     * Wire only the selected UI kit's service provider into the generated
     * project's provider manifest. The starter-kit ships a hard-coded
     * reference to Sahil\Firefiber\FirefighterServiceProvider; every other
     * kit must not reference a package that was not installed, otherwise the
     * application cannot boot.
     */
    private function configureProviders(string $root, string $uiKit): void
    {
        $file = $root . '/bootstrap/providers.php';
        if (!is_file($file)) {
            return;
        }

        $content = file_get_contents($file);
        $firefighter = "    \\Sahil\\Firefiber\\FirefighterServiceProvider::class,
";
        $content = str_replace($firefighter, '', $content);

        $insert = '';
        if ($uiKit === 'firefiber') {
            $insert = $firefighter;
        } elseif ($uiKit === 'react' || $uiKit === 'svelte') {
            $insert = "    \\App\\Providers\\FrontendServiceProvider::class,
";
        }

        if ($insert !== '') {
            $anchor = "    \\App\\Providers\\AssetProvider::class,
";
            if (str_contains($content, $anchor) && !str_contains($content, trim($insert))) {
                $content = str_replace($anchor, $anchor . $insert, $content);
            }
        }

        file_put_contents($file, $content);
    }

    /**
     * @return array<string, mixed>
     */
    private function mergePackage(string $kitFile, string $cssFile): array
    {
        $kit = $this->readJson($kitFile);
        $css = $this->readJson($cssFile);

        $package = [
            'name' => is_string($kit['name'] ?? null) ? $kit['name'] : 'sahilphp-app',
            'private' => true,
            'scripts' => is_array($kit['scripts'] ?? null) ? $kit['scripts'] : [
                'dev' => 'vite',
                'build' => 'vite build',
                'preview' => 'vite preview',
            ],
        ];

        $dependencies = [
            ...($kit['dependencies'] ?? []),
            ...($css['dependencies'] ?? []),
        ];
        $devDependencies = [
            ...($kit['devDependencies'] ?? []),
            ...($css['devDependencies'] ?? []),
        ];

        ksort($dependencies);
        ksort($devDependencies);

        if ($dependencies !== []) {
            $package['dependencies'] = $dependencies;
        }

        if ($devDependencies !== []) {
            $package['devDependencies'] = $devDependencies;
        }

        return $package;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('unable to read [%s].', $path));
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('[%s] is not valid JSON.', $path));
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function encodeJson(array $data): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException('unable to encode generated JSON.');
        }

        return $json."\n";
    }

    private function viteConfig(string $uiKit, string $css): string
    {
        $imports = ["import { defineConfig } from 'vite';"];
        $plugins = [];

        if ($uiKit === 'react') {
            $imports[] = "import react from '@vitejs/plugin-react';";
            $plugins[] = 'react()';
        } elseif ($uiKit === 'vue') {
            $imports[] = "import vue from '@vitejs/plugin-vue';";
            $plugins[] = 'vue()';
        } elseif ($uiKit === 'svelte') {
            $imports[] = "import { svelte } from '@sveltejs/vite-plugin-svelte';";
            $plugins[] = 'svelte()';
        }

        if ($css === 'tailwind') {
            $imports[] = "import tailwindcss from '@tailwindcss/vite';";
            $plugins[] = 'tailwindcss()';
        }

        $pluginList = $plugins === [] ? '[]' : '['.implode(', ', $plugins).']';

        return implode("\n", $imports)."\n\nexport default defineConfig({\n"
            ."  plugins: ".$pluginList.",\n"
            ."  publicDir: false,\n"
            ."  // Keep Vite's cache inside project storage instead of node_modules.\n"
            ."  cacheDir: 'storage/framework/vite',\n"
            ."  build: {\n"
            ."    outDir: 'public/build',\n"
            ."    // Multiple entries may build into the same directory; `ui:build --clean`\n"
            ."    // owns removal instead of Vite emptying outputs it does not produce.\n"
            ."    emptyOutDir: false,\n"
            ."    rollupOptions: {\n"
            ."      input: {\n"
            ."        main: 'resources/js/app.js',\n"
            ."      },\n"
            ."      output: {\n"
            ."        entryFileNames: 'assets/[name].js',\n"
            ."        chunkFileNames: 'assets/[name].js',\n"
            ."        assetFileNames: 'assets/[name][extname]',\n"
            ."      },\n"
            ."    },\n"
            ."  },\n"
            ."  server: {\n"
            ."    host: '127.0.0.1',\n"
            ."    port: 5173,\n"
            ."    watch: {\n"
            ."      ignored: [\n"
            ."        '**/node_modules/**',\n"
            ."        '**/.git/**',\n"
            ."        '**/storage/**',\n"
            ."        '**/public/build/**',\n"
            ."        '**/vendor/**',\n"
            ."      ],\n"
            ."    },\n"
            ."  },\n"
            ."});\n";
    }

    /**
     * @param list<string> $exclude relative paths to skip
     */
    private function copyTree(string $source, string $destination, array $exclude = []): void
    {
        if (!is_dir($source)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($source) + 1));

            foreach ($exclude as $skip) {
                if ($relative === $skip || str_starts_with($relative, $skip.'/')) {
                    continue 2;
                }
            }

            $target = $destination.'/'.$relative;

            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0o775, true) && !is_dir($target)) {
                    throw new RuntimeException(sprintf('unable to create directory [%s].', $target));
                }

                continue;
            }

            $this->copyFile($item->getPathname(), $target, $relative);
        }
    }

    private function copyFile(string $source, string $target, ?string $relative = null): void
    {
        $directory = dirname($target);

        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('unable to create directory [%s].', $directory));
        }

        $contents = @file_get_contents($source);

        if ($contents === false) {
            throw new RuntimeException(sprintf('unable to read [%s].', $source));
        }

        if (@file_put_contents($target, $contents) === false) {
            throw new RuntimeException(sprintf('unable to write [%s].', $target));
        }

        $this->written[] = $relative ?? $target;
    }

    private function write(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('unable to create directory [%s].', $directory));
        }

        if (@file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf('unable to write [%s].', $path));
        }

        $this->written[] = $this->root !== '' && str_starts_with($path, $this->root.'/')
            ? substr($path, strlen($this->root) + 1)
            : $path;
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($path);
    }

    private function isInteractive(): bool
    {
        return function_exists('stream_isatty') && @stream_isatty(STDIN);
    }
}
