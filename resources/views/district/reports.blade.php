@extends('layouts.district')

@section('title', 'District Reports & Analytics')
@section('breadcrumb', 'District Reports')

@section('content')
<div class="grid gap-6">
    <!-- Top Action Bar -->
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">District Academic & Fee Summary Report</h1>
            <p class="text-xs text-slate-500 mt-1">Institutional metrics and revenue tracking for {{ $district->name }} (Session {{ $activeYear->label ?? 'Current' }})</p>
        </div>
        <button type="button" onclick="window.print()" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
            Print / Export Report
        </button>
    </div>

    <!-- Summary KPI Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Total Schools</div>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($stats['total_schools']) }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Total District Students</div>
            <div class="mt-2 text-3xl font-extrabold text-amber-600">{{ number_format($stats['total_students']) }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Verified Fees Deposited</div>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600">Rs {{ number_format($stats['verified_amount']) }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Invoices Awaiting Bank</div>
            <div class="mt-2 text-3xl font-extrabold text-red-500">{{ number_format($stats['pending_invoices']) }}</div>
        </div>
    </div>

    <!-- School-by-School Breakdown Table -->
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 overflow-hidden">
        <div class="p-5 border-b border-amber-100">
            <h3 class="font-bold text-slate-900">Affiliated School Enrollment Distribution</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-700">
                <thead class="bg-amber-50 text-amber-900">
                    <tr>
                        <th class="px-6 py-4 font-bold">School Name</th>
                        <th class="px-6 py-4 font-bold">SEMIS Code</th>
                        <th class="px-6 py-4 font-bold text-center">Total Students</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $s)
                        <tr class="hover:bg-amber-50/40">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $s->name }}</td>
                            <td class="px-6 py-4 font-mono text-slate-600">{{ $s->username }}</td>
                            <td class="px-6 py-4 text-center font-bold text-amber-600">{{ number_format($s->students_count) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $s->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">No schools affiliated in this district.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
