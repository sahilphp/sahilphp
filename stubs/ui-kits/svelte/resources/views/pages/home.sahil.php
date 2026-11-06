@extends('layouts.app')

@section('title')Welcome · {{ app_name ?? 'SahilPHP' }}@endsection

@section('content')
    <section>
        <h1>Welcome to {{ app_name ?? 'SahilPHP' }}</h1>
        <p>
            Your application is running on the SahilPHP framework. Routing, views,
            sessions, migrations, queues and the scheduler are wired up and ready
            for your first feature.
        </p>

        <p>
            <a href="/ui-kit">Explore the UI kit</a>
            <a href="/health">Check health endpoint</a>
        </p>
    </section>
@endsection
