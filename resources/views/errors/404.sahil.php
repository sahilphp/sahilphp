@extends('errors.layout')

@section('title')Page not found@endsection

@section('head')
    <style>
        .error__trail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 22px 0 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .error__trail span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand);
            opacity: 0.18;
        }

        .error__trail span:nth-child(2) { opacity: 0.4; }
        .error__trail span:nth-child(3) { opacity: 0.65; }
        .error__trail em { font-style: normal; margin-left: 4px; }

        .error__routes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 24px 0 0;
        }

        .error__route {
            display: block;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: transparent;
            color: var(--text);
            text-decoration: none;
            transition: border-color 120ms ease, background-color 120ms ease, transform 120ms ease;
        }

        .error__route:hover {
            background: var(--tint);
            border-color: var(--brand);
            transform: translateY(-1px);
        }

        .error__route strong {
            display: block;
            font-size: 15px;
            font-weight: 600;
        }

        .error__route small {
            display: block;
            margin-top: 3px;
            font-size: 12.5px;
            color: var(--muted);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
    </style>
@endsection

@section('content')
    <h1>This page could not be found</h1>
    <p>
        We looked everywhere on this server for the page you asked for, but it does not
        exist or it has been moved. Check the address for a typo, or start again from one
        of these pages.
    </p>

    <div class="error__trail" aria-hidden="true">
        <span></span><span></span><span></span>
        <em>end of the trail</em>
    </div>

    <nav class="error__routes" aria-label="Popular pages">
        <a class="error__route" href="/">
            <strong>Home</strong>
            <small>/</small>
        </a>
        <a class="error__route" href="/ui-kit">
            <strong>UI kit</strong>
            <small>/ui-kit</small>
        </a>
        <a class="error__route" href="/health">
            <strong>Health</strong>
            <small>/health</small>
        </a>
    </nav>
@endsection
