{{-- Shared navigation component for the Tailwind profile. --}}
<header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur">
    <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5" aria-label="Main navigation">
        @include('partials.brand', ['home_url' => home_url ?? '/'])

        <div class="flex items-center gap-1 sm:gap-2">
            <ul class="flex flex-wrap items-center gap-1 sm:gap-2">
                @foreach (links as link)
                    <li>
                        <a class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                            href="{{ link.href }}"
                            @if (link.current ?? false) aria-current="page" @endif>{{ link.label }}</a>
                    </li>
                @endforeach
            </ul>

            @if (cta ?? null)
                <a class="ml-1 hidden rounded-md bg-primary px-3.5 py-2 text-sm font-semibold text-primary-foreground shadow-sm transition hover:opacity-90 sm:inline-flex"
                    href="{{ cta.href }}">{{ cta.label }}</a>
            @endif
        </div>
    </nav>
</header>
