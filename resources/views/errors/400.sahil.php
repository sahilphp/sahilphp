@extends('errors.layout')

@section('title'){{ __('errors.bad_request_title') }}@endsection

@section('content')
    <h1>{{ __('errors.bad_request_title') }}</h1>
    <p>{{ __('errors.bad_request_detail') }}</p>
@endsection
