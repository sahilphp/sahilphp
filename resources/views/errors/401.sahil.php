@extends('errors.layout')

@section('title'){{ __('errors.signin_required_title') }}@endsection

@section('content')
    <h1>{{ __('errors.signin_required_title') }}</h1>
    <p>{{ __('errors.signin_required_detail') }}</p>
@endsection

@section('actions')
    <a class="error__link" href="/login">Sign in</a>
@endsection
