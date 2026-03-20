@extends('layouts.app')

@section('title','QR Scanner')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400 animate-pulse">QR Scanner</h1>

<div class="flex items-center justify-center h-96">
    <div class="border-4 border-cyan-400 w-72 h-72 rounded-lg animate-pulse flex items-center justify-center text-gray-300">
        Camera View (simulated)
    </div>
</div>

<p class="mt-4 text-gray-400">Scanned Document: #123</p>
@endsection