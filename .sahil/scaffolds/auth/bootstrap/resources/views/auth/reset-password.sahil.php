@extends('layouts.auth')

@section('title')Reset password · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <div class="text-center mb-4">
        <h1 class="h4 fw-semibold mb-1">Choose a new password</h1>
        <p class="text-muted small mb-0">
            Your reset link is single use. After saving, sign in with the new password.
        </p>
    </div>

    @if (status ?? null)
        <div class="alert alert-success py-2 small" role="status">{{ status }}</div>
    @endif

    <form method="post" action="{{ link('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ token ?? '' }}">

        @if (error('token', 'reset'))
            <div class="alert alert-danger py-2 small" role="alert">{{ error('token', 'reset') }}</div>
        @endif

        <div class="mb-3">
            <label class="form-label small fw-medium" for="email">Email</label>
            <input class="form-control@if (error('email', 'reset')) is-invalid@endif" id="email" name="email" type="email"
                value="{{ old.email ?? '' }}" placeholder="you@example.com" autocomplete="email" required>
            @if (error('email', 'reset'))
                <div class="invalid-feedback" id="email-error">{{ error('email', 'reset') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium" for="password">New password</label>
            <input class="form-control@if (error('password', 'reset')) is-invalid@endif" id="password" name="password" type="password"
                placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
            @if (error('password', 'reset'))
                <div class="invalid-feedback" id="password-error">{{ error('password', 'reset') }}</div>
            @endif
        </div>

        <div class="mb-4">
            <label class="form-label small fw-medium" for="password_confirmation">Confirm new password</label>
            <input class="form-control@if (error('password_confirmation', 'reset')) is-invalid@endif" id="password_confirmation" name="password_confirmation" type="password"
                placeholder="Repeat your password" autocomplete="new-password" required>
            @if (error('password_confirmation', 'reset'))
                <div class="invalid-feedback" id="password_confirmation-error">{{ error('password_confirmation', 'reset') }}</div>
            @endif
        </div>

        <button class="btn btn-primary w-100" type="submit">Reset password</button>
    </form>

    <p class="text-center small mt-4 mb-0">
        <a class="text-decoration-none fw-medium" href="{{ link('login') }}">← Back to sign in</a>
    </p>
@endsection
