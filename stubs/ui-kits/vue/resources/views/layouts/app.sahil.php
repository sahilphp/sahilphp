{{--
    Application layout.

    The CSS framework stub overrides this file with its own styled layout.
    This neutral shell is the fallback when a UI kit is installed on its own.
--}}
<!doctype html>
<html lang="{{ lang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="SahilPHP">
    <title>@yield('title', app_name ?? 'SahilPHP')</title>

    @asset_client
    @asset_css('main')
    @yield('head')
</head>
<body>
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

    <main class="container">
        @yield('content')
    </main>

    @asset_js('app')
    @yield('scripts')
</body>
</html>
