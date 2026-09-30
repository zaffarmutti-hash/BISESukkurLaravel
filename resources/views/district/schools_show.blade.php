@extends('layouts.district')

@section('title', $school->name)
@section('breadcrumb', 'School Profile')

@section('content')
<div class="grid gap-6">
    <!-- Header Card -->
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $school->name }}</h1>
                    @if($school->is_active)
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">ACTIVE</span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">INACTIVE</span>
                    @endif
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-500">
                    <span>SEMIS Code: <strong class="font-mono text-slate-800">{{ $school->username }}</strong></span>
                    <span>District: <strong class="text-slate-800">{{ $district->name }}</strong></span>
                    <span>Email: <strong class="text-slate-800">{{ $school->email ?? 'N/A' }}</strong></span>
                </div>
            </div>
            <div>
                <a href="{{ route('district.schools') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    ← Back to Schools
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Total Enrolled</div>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($school->students_count) }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Total Invoices</div>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($school->invoices_count) }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">Principal / Head</div>
            <div class="mt-2 text-base font-bold text-slate-800">{{ $school->principal_name ?? 'Principal' }}</div>
            <div class="text-xs text-slate-400">{{ $school->principal_phone ?? 'No phone' }}</div>
        </div>

        <div class="card bg-white rounded-2xl shadow border border-amber-100 p-5">
            <div class="text-xs uppercase font-bold text-slate-400">School Type</div>
            <div class="mt-2 text-base font-bold text-slate-800">{{ ucfirst($school->type ?? 'Government') }}</div>
            <div class="text-xs text-slate-400">{{ ucfirst($school->gender_type ?? 'Co-education') }}</div>
        </div>
    </div>

    <!-- Recent Invoices & Students -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Invoices -->
        <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 overflow-hidden">
            <div class="p-5 border-b border-amber-100">
                <h3 class="font-bold text-slate-900">Recent Challan Invoices</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs text-slate-700">
                    <thead class="bg-amber-50 text-amber-900">
                        <tr>
                            <th class="px-4 py-3">Invoice #</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold">{{ $inv->invoice_number }}</td>
                                <td class="px-4 py-3">{{ ucfirst($inv->invoice_type) }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold">Rs {{ number_format($inv->total_amount_paisas / 100) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2 py-0.5 font-semibold text-[10px] {{ in_array($inv->status, ['confirmed', 'verified']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ strtoupper($inv->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400">No invoices generated yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Students -->
        <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 overflow-hidden">
            <div class="p-5 border-b border-amber-100">
                <h3 class="font-bold text-slate-900">Recently Enrolled Students</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs text-slate-700">
                    <thead class="bg-amber-50 text-amber-900">
                        <tr>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Enrollment #</th>
                            <th class="px-4 py-3">Father Name</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentStudents as $std)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $std->full_name }}</td>
                                <td class="px-4 py-3 font-mono">{{ $std->enrollment_number ?? 'Pending' }}</td>
                                <td class="px-4 py-3">{{ $std->father_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">No students enrolled yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
