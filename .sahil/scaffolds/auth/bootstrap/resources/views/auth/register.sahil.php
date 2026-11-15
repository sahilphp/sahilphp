@extends('layouts.auth')

@section('title')Create account · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <div class="text-center mb-4">
        <h1 class="h4 fw-semibold mb-1">Create your account</h1>
        <p class="text-muted small mb-0">Start building on SahilPHP in under a minute.</p>
    </div>

    @if (status ?? null)
        <div class="alert alert-success py-2 small" role="status">{{ status }}</div>
    @endif

    <form method="post" action="{{ link('register.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label small fw-medium" for="name">Full name</label>
            <input class="form-control@if (error('name', 'register')) is-invalid@endif" id="name" name="name" type="text"
                value="{{ old.name ?? '' }}" placeholder="Ada Lovelace" autocomplete="name" required>
            @if (error('name', 'register'))
                <div class="invalid-feedback" id="name-error">{{ error('name', 'register') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium" for="email">Work email</label>
            <input class="form-control@if (error('email', 'register')) is-invalid@endif" id="email" name="email" type="email"
                value="{{ old.email ?? '' }}" placeholder="you@example.com" autocomplete="email" required>
            @if (error('email', 'register'))
                <div class="invalid-feedback" id="email-error">{{ error('email', 'register') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium" for="password">Password</label>
            <input class="form-control@if (error('password', 'register')) is-invalid@endif" id="password" name="password" type="password"
                placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
            @if (error('password', 'register'))
                <div class="invalid-feedback" id="password-error">{{ error('password', 'register') }}</div>
            @else
                <div class="form-text">Use at least 8 characters, including a number.</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium" for="password_confirmation">Confirm password</label>
            <input class="form-control@if (error('password_confirmation', 'register')) is-invalid@endif" id="password_confirmation" name="password_confirmation" type="password"
                placeholder="Repeat your password" autocomplete="new-password" required>
            @if (error('password_confirmation', 'register'))
                <div class="invalid-feedback" id="password_confirmation-error">{{ error('password_confirmation', 'register') }}</div>
            @endif
        </div>

        <div class="form-check mb-1">
            <input class="form-check-input@if (error('terms', 'register')) is-invalid@endif" id="terms" name="terms" type="checkbox" value="1"@if (old.terms ?? false) checked@endif>
            <label class="form-check-label small" for="terms">
                I agree to the <a class="text-decoration-none" href="#">terms</a> and
                <a class="text-decoration-none" href="#">privacy policy</a>.
            </label>
            @if (error('terms', 'register'))
                <div class="invalid-feedback" id="terms-error">{{ error('terms', 'register') }}</div>
            @endif
        </div>

        <button class="btn btn-primary w-100 mt-3" type="submit">Create account</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Already have an account? <a class="text-decoration-none fw-medium" href="{{ link('login') }}">Sign in</a>
    </p>
@endsection
