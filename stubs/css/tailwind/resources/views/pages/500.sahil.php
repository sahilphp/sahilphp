@extends('layouts.app')

@section('title')Server Error · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <div class="max-w-2xl mx-auto py-12 text-center">
        <h1 class="text-3xl font-bold text-slate-900 mb-4">500 — Server Error</h1>
        <p class="text-slate-600 mb-6">Something went wrong on our end.</p>
        <a href="/" class="btn-primary">Go home</a>
    </div>
@endsection