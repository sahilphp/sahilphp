@extends('layouts.app')

@section('title')Not Found · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <div class="max-w-2xl mx-auto py-12 text-center">
        <h1 class="text-3xl font-bold text-slate-900 mb-4">404 — Page Not Found</h1>
        <p class="text-slate-600 mb-6">The page you are looking for does not exist.</p>
        <a href="/" class="btn-primary">Go home</a>
    </div>
@endsection