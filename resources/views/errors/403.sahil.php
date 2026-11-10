@extends('errors.layout')

@section('title')Access denied@endsection

@section('head')
    <style>
        .error__lock {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            margin: 2px 0 16px;
            border-radius: 16px;
            background: var(--tint);
            color: var(--brand);
        }

        .error__lock span {
            position: relative;
            display: block;
            width: 22px;
            height: 16px;
            margin-top: 7px;
            border-radius: 4px;
            background: currentColor;
        }

        .error__lock span::before {
            content: "";
            position: absolute;
            left: 3px;
            top: -9px;
            width: 16px;
            height: 12px;
            border: 2.5px solid currentColor;
            border-bottom: none;
            border-radius: 9px 9px 0 0;
        }

        .error__lock span::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 5px;
            width: 3px;
            height: 6px;
            margin-left: -1.5px;
            border-radius: 2px;
            background: var(--tint);
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
    </style>
@endsection

@section('content')
    <div class="error__lock" aria-hidden="true"><span></span></div>

    <h1>You do not have access to this page</h1>
    <p>
        Your account is recognised, but it is not allowed to open this page. Nothing is
        broken — this is a permissions decision, not an error on your side.
    </p>

    <ul class="error__notes">
        <li>If this page is yours, sign in with an account that has the required role.</li>
        <li>Otherwise ask the site administrator to grant your account access.</li>
        <li>Followed a link to get here? It may be out of date or belong to another team.</li>
    </ul>
@endsection
