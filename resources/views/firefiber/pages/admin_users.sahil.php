{{-- Firefiber page template: `App\Firefiber\Pages\AdminUsersPage` resolves
    here by convention (firefiber.pages.admin_users). The same template is
    reachable through `Render::fire('admin_users')`. --}}
@extends('layouts.app')

@section('title'){{ page_title ?? 'Admin Users' }} · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <h1 class="h3 mb-4">{{ page_title ?? 'Admin Users' }}</h1>

    @foreach (components ?? [] as component)
        {!! component !!}
    @endforeach
@endsection
