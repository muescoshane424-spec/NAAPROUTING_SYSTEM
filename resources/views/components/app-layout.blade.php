@extends('layouts.app')

@section('title')
    {{ $title ?? 'NAAP Routing System' }}
@endsection

@section('content')
    @isset($header)
        <div class="mb-4">
            {{ $header }}
        </div>
    @endisset

    {{ $slot }}
@endsection
