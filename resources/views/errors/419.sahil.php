@extends('errors.layout')

@section('title')Page expired@endsection

@section('head')
    <style>
        .error__ticket {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 22px 0 0;
            padding: 16px 18px;
            border: 1px dashed var(--line);
            border-radius: 14px;
            background: var(--tint);
        }

        .error__ticket-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .error__ticket-value {
            margin-top: 4px;
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        .error__stamp {
            flex: none;
            padding: 5px 11px;
            border: 2px solid var(--brand);
            border-radius: 8px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transform: rotate(-4deg);
        }

        .error__notes {
            display: grid;
            gap: 10px;
            margin: 22px 0 0;
            padding: 0;
            list-style: none;
        }

        .error__notes li {
            position: relative;
            padding-left: 22px;
            font-size: 15px;
            line-height: 1.6;
            color: var(--muted);
        }

        .error__notes li::before {
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

        .error__reload {
            display: inline-flex;
            align-items: center;
            gap: 7px;
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

        @media (max-width: 520px) {
            .error__ticket { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
@endsection

@section('content')
    <h1>Your page has expired</h1>
    <p>
        You were away long enough that this page's security token stopped being valid, so
        the form could not be submitted. Nothing was sent and nothing was changed.
    </p>

    <div class="error__ticket">
        <div>
            <div class="error__ticket-label">Session token</div>
            <div class="error__ticket-value">sahil_session · expired</div>
        </div>
        <span class="error__stamp">expired</span>
    </div>

    <ul class="error__notes">
        <li>Reload the page and submit the form again — the refreshed token is valid.</li>
        <li>If the form was long, keep a copy of what you typed before reloading.</li>
        <li>Signed out in another tab? Signing in again resets the session.</li>
    </ul>
@endsection

@section('actions')
    <a class="error__link" href="">Reload this page</a>
@endsection
