@extends('layouts.auth')

@section('title')Sign in · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <div class="text-center mb-4">
        <h1 class="h4 fw-semibold mb-1">Sign in</h1>
        <p class="text-muted small mb-0">Enter your credentials to open your dashboard.</p>
    </div>

    @if (status ?? null)
        <div class="alert alert-success py-2 small" role="status">{{ status }}</div>
    @endif

    <form method="post" action="{{ link('login.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label small fw-medium" for="email">Email</label>
            <input class="form-control@if (error('email', 'login')) is-invalid@endif" id="email" name="email" type="email"
                value="{{ old.email ?? '' }}" placeholder="you@example.com" autocomplete="email" required>
            @if (error('email', 'login'))
                <div class="invalid-feedback" id="email-error">{{ error('email', 'login') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <label class="form-label small fw-medium mb-0" for="password">Password</label>
                <a class="small text-decoration-none" href="{{ link('password.request') }}">Forgot password?</a>
            </div>
            <input class="form-control mt-1@if (error('password', 'login')) is-invalid@endif" id="password" name="password" type="password"
                placeholder="••••••••" autocomplete="current-password" required>
            @if (error('password', 'login'))
                <div class="invalid-feedback" id="password-error">{{ error('password', 'login') }}</div>
            @endif
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1"@if (old.remember ?? false) checked@endif>
            <label class="form-check-label small" for="remember">Remember me for 30 days</label>
        </div>

        <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Don't have an account? <a class="text-decoration-none fw-medium" href="{{ link('register') }}">Create one</a>
    </p>
@endsection
