@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400 animate-pulse">Documents</h1>

<div class="mb-4">
    <a href="{{ route('documents.create') }}" class="bg-purple-500 hover:bg-purple-600 py-2 px-4 rounded-lg transition">Route New Document</a>
</div>

<table class="min-w-full bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden">
    <thead class="bg-white/5">
        <tr class="text-gray-300">
            <th class="px-6 py-3">ID</th>
            <th class="px-6 py-3">Title</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ([
            ['id'=>'001','title'=>'Document A','status'=>'In Transit','color'=>'text-purple-400'],
            ['id'=>'002','title'=>'Document B','status'=>'Completed','color'=>'text-green-400'],
            ['id'=>'003','title'=>'Document C','status'=>'Pending','color'=>'text-yellow-400'],
        ] as $doc)
        <tr class="hover:bg-white/20 transition">
            <td class="px-6 py-3">{{$doc['id']}}</td>
            <td class="px-6 py-3">{{$doc['title']}}</td>
            <td class="px-6 py-3 font-bold {{$doc['color']}}">{{$doc['status']}}</td>
            <td class="px-6 py-3">
                <a href="{{ route('documents.create') }}" class="text-cyan-400 hover:underline">Route</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection