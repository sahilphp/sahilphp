{{-- Tailwind CSS v4 layout. Extends the neutral base structure with utility classes. --}}
<!doctype html>
<html lang="{{ lang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="SahilPHP">
    <meta name="theme-color" content="#0b6fe8">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', app_name ?? 'SahilPHP')</title>

    {{-- Vite HMR client (development only) and the active profile's CSS. --}}
    @asset_client
    @asset_css('main')
    @asset_css('@fiberstyles')

    {{-- Page-specific styles: define a `head` section in the page. --}}
    @yield('head')
</head>
<body class="@yield('body_class', 'min-h-screen bg-white text-slate-900 antialiased')">
    @section('nav')
        @include('partials.nav', [
            'links' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'UI kit', 'href' => '/ui-kit'],
                ['label' => 'Health', 'href' => '/health'],
            ],
        ])
    @endsection

    @yield('nav')

    <main class="@yield('main_class', 'mx-auto w-full max-w-6xl px-4 py-10')">
        @yield('content')
    </main>

    @section('footer')
        <footer class="mt-20 border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-5 px-4 py-8 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3">
                    @include('partials.brand', ['variant' => 'mark'])
                    <p class="text-sm text-slate-500">Built with SahilPHP.</p>
                </div>

                <nav class="flex flex-wrap items-center gap-6 text-sm text-slate-500" aria-label="Footer navigation">
                    <a class="transition hover:text-slate-900" href="/">Home</a>
                    <a class="transition hover:text-slate-900" href="/ui-kit">UI kit</a>
                    <a class="transition hover:text-slate-900" href="/health">Health</a>
                </nav>
            </div>
        </footer>
    @endsection

    @yield('footer')

    {{-- Profile JavaScript, then page-specific scripts. --}}
    @asset_js('main')
    @asset_js('@fiberscripts')
    @yield('scripts')
</body>
</html>
