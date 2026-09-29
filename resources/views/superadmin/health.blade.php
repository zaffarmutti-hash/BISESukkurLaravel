@extends('layouts.superadmin')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <article class="card p-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Overall</p>
                <div class="mt-4 flex items-center gap-3">
                    <span class="inline-flex h-3.5 w-3.5 rounded-full {{ $health['healthy'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    <span class="text-base font-semibold {{ $health['healthy'] ? 'text-emerald-700' : 'text-rose-700' }}">{{ $health['healthy'] ? 'Healthy' : 'Issues Detected' }}</span>
                </div>
            </article>

            <article class="card p-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Database</p>
                <p class="mt-4 text-base font-semibold {{ $health['database'] ? 'text-emerald-700' : 'text-rose-700' }}">{{ $health['database'] ? 'Connected' : 'Failed' }}</p>
            </article>

            <article class="card p-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Storage</p>
                <p class="mt-4 text-base font-semibold {{ $health['disk'] ? 'text-emerald-700' : 'text-amber-700' }}">{{ $health['disk'] ? 'Adequate' : 'Low Space' }}</p>
                @if(isset($health['disk_free']))
                    <p class="mt-2 text-sm text-slate-500">{{ $health['disk_free'] }} GB free</p>
                @endif
            </article>
        </section>

        @if(!$health['healthy'] && !empty($health['issues']))
            <section class="card p-6 rounded-3xl border border-rose-100 bg-rose-50 shadow-sm">
                <h2 class="text-lg font-semibold text-rose-700">Active Issues</h2>
                <ul class="mt-4 list-disc pl-5 space-y-2 text-sm text-rose-700">
                    @foreach($health['issues'] as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
