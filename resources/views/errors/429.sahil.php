@extends('errors.layout')

@section('title'){{ __('errors.too_many_title') }}@endsection

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
    <h1>{{ __('errors.too_many_title') }}</h1>
    <p>{{ __('errors.too_many_detail') }}</p>

    <div class="error__meter" role="img" aria-label="Request budget exhausted">
        <div class="error__meter-label">
            <span>{{ __('errors.ratelimit_budget') }}</span>
            <span>{{ __('errors.ratelimit_exhausted') }}</span>
        </div>
        <div class="error__ticks" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span class="spent"></span><span class="spent"></span><span class="spent"></span>
        </div>
    </div>

    <ul class="error__tips">
        <li>{!! __('errors.ratelimit_step_wait') !!}</li>
        <li>{{ __('errors.ratelimit_step_share') }}</li>
        <li>{!! __('errors.ratelimit_step_retry') !!}</li>
    </ul>
@endsection

@section('actions')
    <a class="error__link" href="">Try again</a>
@endsection
