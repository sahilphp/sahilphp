@extends('layouts.app')

@section('title')Welcome · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <section class="relative isolate overflow-hidden">
        <div class="hero-glow pointer-events-none absolute inset-x-0 -top-24 -z-10 h-72"></div>

        <div class="mx-auto max-w-3xl px-2 pb-4 pt-6 text-center sm:pt-10">
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Application ready
            </span>

            <img class="mx-auto mt-8 h-16 w-auto" src="{{ asset('logo.png') }}" alt="SahilPHP"
                width="380" height="160" decoding="async">

            <h1 class="mt-6 text-balance text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                Welcome to
                <span class="bg-gradient-to-r from-primary to-sky-400 bg-clip-text text-transparent">{{ app_name ?? 'SahilPHP' }}</span>
            </h1>

            <p class="mx-auto mt-4 max-w-xl text-pretty text-base text-slate-600 sm:text-lg">
                Your application runs on the SahilPHP framework with Tailwind CSS v4.
                Routing, views, sessions, migrations, queues and the scheduler are already wired up.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition hover:opacity-90"
                    href="/ui-kit">
                    Explore the UI kit
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.19L10.22 5.53a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 1 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                    </svg>
                </a>

                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    href="/health">Check health endpoint</a>
            </div>

            <p class="mt-5 text-xs text-slate-400">
                PHP {{ php_version ?? '' }} · {{ profile_label ?? 'Tailwind CSS v4 profile' }}
            </p>
        </div>
    </section>

    <section class="mx-auto mt-6 max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Useful commands">
        <div class="flex items-center gap-1.5 border-b border-slate-200 bg-slate-50/80 px-4 py-2.5">
            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
            <span class="ml-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Terminal</span>
        </div>

        <ul class="divide-y divide-slate-100 px-4 py-1 font-mono text-[13px]">
            <li class="flex items-center gap-3 py-2.5">
                <span class="text-slate-300">$</span>
                <span class="text-slate-700">php sahil start</span>
                <span class="ml-auto font-sans text-xs text-slate-400">dev server</span>
            </li>
            <li class="flex items-center gap-3 py-2.5">
                <span class="text-slate-300">$</span>
                <span class="text-slate-700">php sahil make:auth</span>
                <span class="ml-auto font-sans text-xs text-slate-400">auth pages</span>
            </li>
            <li class="flex items-center gap-3 py-2.5">
                <span class="text-slate-300">$</span>
                <span class="text-slate-700">php sahil ui:dev</span>
                <span class="ml-auto font-sans text-xs text-slate-400">vite</span>
            </li>
        </ul>
    </section>

    <section class="mt-16">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Everything is already connected</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Start in <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[0.8em]">routes/web.php</code>,
                    build views in <code class="rounded bg-slate-100 px-1.5 py-0.5 text-[0.8em]">resources/views</code>.
                </p>
            </div>
            <a class="text-sm font-medium text-primary hover:underline" href="/ui-kit">See the components →</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h10a4 4 0 0 1 0 8H8" /><path d="M11 18l-3 3 3 3" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-semibold text-slate-900">Routing &amp; HTTP</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                    Named routes, middleware, CSRF protection and JSON responses are registered at boot.
                </p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="13" rx="2" /><path d="M8 21h8M12 17v4" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-semibold text-slate-900">Templates &amp; views</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                    Layouts, sections and partials with escaping by default and compiled views cached on disk.
                </p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-semibold text-slate-900">Queues &amp; scheduler</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                    Background jobs, retries and scheduled tasks run through one CLI entry point.
                </p>
            </article>
        </div>
    </section>
@endsection
