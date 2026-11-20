@extends('layouts.app')

@section('title'){{ page_title }} · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <section {!! firefiber_attributes !!}>
        <h1 class="home-title">{{ page_title }}</h1>
        <p>Your {{ app_name ?? 'SahilPHP' }} application is ready — this is a
            Firefiber page mounted with <code>Link::firefiber()</code>.</p>

        <button type="button" fiber:click="increment" fiber:loading.attr="disabled">
            Clicked <span class="visit-count" data-firefiber-bind="visits">0</span> times
        </button>

        @foreach (components as component)
            {!! component !!}
        @endforeach
    </section>
@endsection
