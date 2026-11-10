@extends('errors.layout')

@section('title')Server error@endsection

@section('head')
    <style>
        .error__steps {
            margin: 24px 0 0;
            padding: 0;
            list-style: none;
            counter-reset: error-step;
        }

        .error__steps li {
            counter-increment: error-step;
            position: relative;
            margin: 0 0 12px;
            padding: 0 0 0 40px;
            font-size: 15px;
            line-height: 1.6;
            color: var(--muted);
        }

        .error__steps li::before {
            content: counter(error-step);
            position: absolute;
            left: 0;
            top: -1px;
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--tint);
            color: var(--brand);
            font-size: 12.5px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .error__steps strong { color: var(--text); font-weight: 600; }

        .error__reload {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--brand);
            text-decoration: none;
        }

        .error__reload:hover { text-decoration: underline; }

        .error__reload::before {
            content: "";
            width: 13px;
            height: 13px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
        }
    </style>
@endsection

@section('content')
    <h1>Something went wrong on our end</h1>
    <p>
        An unexpected error stopped this page from finishing. It is not something you did,
        and nothing you submitted has been lost.
    </p>

    <ol class="error__steps">
        <li><strong>Wait a moment.</strong> Most of these errors clear on their own within a minute.</li>
        <li><strong>Reload the page.</strong> A fresh request often succeeds where the last one failed.</li>
        <li><strong>If it keeps happening,</strong> contact the site administrator and mention the
            status code <strong>500</strong> — the details have already been logged.</li>
    </ol>

    <a class="error__reload" href="">Reload this page</a>
@endsection
