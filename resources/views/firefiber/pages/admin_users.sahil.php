{{-- Firefiber page template: `App\Firefiber\Pages\AdminUsersPage` resolves
    here by convention (firefiber.pages.admin_users) when mounted through
    `Link::firefiber()`. `firefiber_attributes` marks the page's reactive
    root and carries its signed snapshot. --}}
@extends('layouts.app')

@section('title'){{ page_title ?? 'Admin Users' }} · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <section {!! firefiber_attributes !!}>
        <h1 class="h3 mb-4">{{ page_title ?? 'Admin Users' }}</h1>

        @foreach (components ?? [] as component)
            {!! component !!}
        @endforeach
    </section>
@endsection
