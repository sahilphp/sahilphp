@extends('errors.layout')

@section('title')Too many requests@endsection

@section('head')
    <style>
        .error__meter {
            display: grid;
            gap: 6px;
            margin: 24px 0 6px;
        }

        .error__ticks {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 5px;
        }

        .error__ticks span {
            height: 30px;
            border-radius: 6px;
            background: var(--brand);
        }

        .error__ticks span.spent {
            background: var(--tint);
            box-shadow: inset 0 0 0 1px var(--line);
        }

        .error__meter-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .error__tips {
            display: grid;
            gap: 10px;
            margin: 22px 0 0;
            padding: 0;
            list-style: none;
        }

        .error__tips li {
            position: relative;
            padding-left: 22px;
            font-size: 15px;
            line-height: 1.6;
            color: var(--muted);
        }

        .error__tips li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 8px;
            width: 8px;
            height: 8px;
            border: 2px solid var(--brand);
            border-radius: 50%;
            opacity: 0.6;
        }

        .error__tips code {
            padding: 1px 5px;
            border-radius: 5px;
            background: var(--tint);
            color: var(--text);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
        }

        @media (max-width: 520px) {
            .error__ticks span { height: 22px; }
        }
    </style>
@endsection

@section('content')
    <h1>You are sending requests a little too fast</h1>
    <p>
        This address limits how many requests one client may make in a short window, and
        your window is full. Nothing is broken — the limit resets on its own in a moment.
    </p>

    <div class="error__meter" role="img" aria-label="Request budget exhausted">
        <div class="error__meter-label">
            <span>Request budget</span>
            <span>Exhausted</span>
        </div>
        <div class="error__ticks" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span class="spent"></span><span class="spent"></span><span class="spent"></span>
        </div>
    </div>

    <ul class="error__tips">
        <li><strong>Wait a few seconds</strong>, then reload this page once — not repeatedly.</li>
        <li>If you share this connection, another device may be using the same budget.</li>
        <li>Calling the API? Honour the <code>Retry-After</code> response header before the next attempt.</li>
    </ul>
@endsection

@section('actions')
    <a class="error__link" href="">Try again</a>
@endsection
