@extends('layouts.app')

@section('title','Route Document')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Route Document</h1>

<form class="space-y-4" method="POST" action="{{ route('documents.store') }}">
    @csrf
    <input type="text" name="title" placeholder="Document Title" class="w-full p-3 rounded-lg bg-[#1f2937]">
    <textarea name="description" placeholder="Description" class="w-full p-3 rounded-lg bg-[#1f2937]"></textarea>
    <select name="origin" class="w-full p-3 rounded-lg bg-[#1f2937]">
        <option>Registrar</option>
        <option>Accounting</option>
        <option>Dean's Office</option>
    </select>
    <select name="destination" class="w-full p-3 rounded-lg bg-[#1f2937]">
        <option>Registrar</option>
        <option>Accounting</option>
        <option>Dean's Office</option>
    </select>
    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 py-2 px-4 rounded-lg transition">Submit</button>
</form>

<div class="mt-6 p-6 bg-white/10 backdrop-blur-lg rounded-xl animate-pulse text-gray-400">
    QR Code Preview (animated neon)
</div>
@endsection