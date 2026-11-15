@extends('errors.layout')

@section('title'){{ __('errors.unavailable_title') }}@endsection

@section('content')
    <h1>{{ __('errors.unavailable_title') }}</h1>
    <p>{{ __('errors.unavailable_detail') }}</p>
@endsection
