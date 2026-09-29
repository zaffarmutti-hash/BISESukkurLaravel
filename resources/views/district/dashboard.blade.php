@extends('layouts.district')

@section('content')
    <div class="grid gap-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="card bg-amber-500 text-white p-6 rounded-3xl shadow-lg">
                <p class="text-sm uppercase tracking-[0.24em] opacity-80">Total Schools</p>
                <p class="mt-4 text-4xl font-extrabold">{{ number_format($total_schools) }}</p>
                <p class="mt-2 text-sm text-amber-100/90">Active: {{ number_format($active_schools) }}</p>
            </article>

            <article class="card bg-orange-500 text-white p-6 rounded-3xl shadow-lg">
                <p class="text-sm uppercase tracking-[0.24em] opacity-80">Total Students</p>
                <p class="mt-4 text-4xl font-extrabold">{{ number_format($total_students) }}</p>
            </article>

            <article class="card bg-emerald-500 text-white p-6 rounded-3xl shadow-lg">
                <p class="text-sm uppercase tracking-[0.24em] opacity-80">Verified Amount</p>
                <p class="mt-4 text-4xl font-extrabold">Rs {{ number_format($verified_amount) }}</p>
            </article>

            <article class="card {{ $pending_verifications > 0 ? 'bg-red-500' : 'bg-slate-600' }} text-white p-6 rounded-3xl shadow-lg">
                <p class="text-sm uppercase tracking-[0.24em] opacity-80">Pending Verifications</p>
                <p class="mt-4 text-4xl font-extrabold">{{ number_format($pending_verifications) }}</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.55fr_1fr]">
            <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 overflow-hidden">
                <div class="p-6 border-b border-amber-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Schools Overview</h2>
                        <p class="mt-1 text-sm text-slate-600">Top district schools and latest verification status.</p>
                    </div>
                    <a href="{{ route('district.schools') }}" class="text-amber-600 font-semibold">View All →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-amber-50 text-amber-900">
                            <tr>
                                <th class="px-5 py-4">School</th>
                                <th class="px-5 py-4">SEMIS Code</th>
                                <th class="px-5 py-4">Students</th>
                                <th class="px-5 py-4">Exam Forms</th>
                                <th class="px-5 py-4">Verified</th>
                                <th class="px-5 py-4">Pending</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($schools as $school)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $school['name'] }}</td>
                                    <td class="px-5 py-4 font-mono text-slate-600">{{ $school['semis_code'] }}</td>
                                    <td class="px-5 py-4 font-semibold">{{ number_format($school['total_students']) }}</td>
                                    <td class="px-5 py-4">{{ number_format($school['exam_form_count']) }}</td>
                                    <td class="px-5 py-4 text-emerald-700 font-semibold">Rs {{ number_format($school['verified_amount']) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $school['pending_verification'] > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ number_format($school['pending_verification']) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 space-y-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $school['enrollment_open'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">Enrollment {{ $school['enrollment_open'] ? 'Open' : 'Closed' }}</span>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $school['exam_open'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">Exam {{ $school['exam_open'] ? 'Open' : 'Closed' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('district.schools.show', ['school' => $school['id']]) }}" class="text-amber-600 font-semibold">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card bg-white rounded-3xl shadow-lg border border-amber-100">
                <div class="p-6 border-b border-amber-100">
                    <h2 class="text-xl font-semibold text-slate-900">Announcements</h2>
                    <p class="mt-1 text-sm text-slate-600">Latest district notices.</p>
                </div>
                <div class="p-6 space-y-4 max-h-[520px] overflow-y-auto">
                    @foreach($announcements as $announcement)
                        <div class="rounded-3xl border p-5 {{ $announcement['is_unread'] ? 'border-amber-300 bg-amber-50' : 'border-slate-100 bg-white' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-slate-900">{{ $announcement['title'] }}</h3>
                                    <p class="mt-2 text-sm text-slate-700">{{ $announcement['content'] }}</p>
                                </div>
                                @if($announcement['is_unread'])
                                    <span class="mt-1 inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                                @endif
                            </div>
                            <div class="mt-4 text-xs uppercase tracking-[0.22em] text-slate-400">{{ $announcement['created_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
