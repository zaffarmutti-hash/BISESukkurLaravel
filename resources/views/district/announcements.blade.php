@extends('layouts.district')

@section('title', 'Official Announcements & Circulars')
@section('breadcrumb', 'District Circulars')

@section('content')
<div class="grid gap-6">
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 p-6">
        <h1 class="text-xl font-bold text-slate-900">Board Circulars & Official Announcements</h1>
        <p class="text-xs text-slate-500 mt-1">Official directives, schedule releases, and policies issued by BISE Sukkur for {{ $district->name }}</p>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $ann)
            <div class="card bg-white rounded-3xl shadow border border-amber-100 p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-3">
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">BOARD CIRCULAR</span>
                    <span class="text-xs font-semibold text-slate-400">{{ $ann->created_at?->format('d M Y, h:i A') }}</span>
                </div>
                <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $ann->title }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $ann->content }}</p>
            </div>
        @empty
            <div class="card bg-white rounded-3xl shadow border border-amber-100 p-10 text-center text-slate-400">
                No active announcements for this district at this time.
            </div>
        @endforelse

        <div class="p-4">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
