@extends('layouts.district')

@section('title', 'Affiliated Schools')
@section('breadcrumb', 'Schools Directory')

@section('content')
<div class="grid gap-6">
    <!-- Header Filter & Search -->
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 p-6">
        <form method="GET" action="{{ route('district.schools') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[280px]">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search school name or SEMIS code..."
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 flex-1 min-w-[200px]"
                />
                <select name="is_active" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-amber-700 transition">
                    Search
                </button>
            </div>
            <div class="text-sm text-slate-500">
                Total: <strong class="text-slate-900">{{ $schools->total() }}</strong> institutions
            </div>
        </form>
    </div>

    <!-- Schools List -->
    <div class="card bg-white rounded-3xl shadow-lg border border-amber-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-700">
                <thead class="bg-amber-50 text-amber-900">
                    <tr>
                        <th class="px-6 py-4 font-bold">School Name</th>
                        <th class="px-6 py-4 font-bold">SEMIS Code</th>
                        <th class="px-6 py-4 font-bold text-center">Enrolled Students</th>
                        <th class="px-6 py-4 font-bold text-center">Invoices</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $school)
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $school->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $school->email ?? 'No email on record' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-600">
                                {{ $school->username }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-900">
                                {{ number_format($school->students_count ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $school->invoices_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($school->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('district.schools.show', $school->id) }}" class="rounded-xl border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800 hover:bg-amber-100 transition">
                                    Details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                No schools found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100">
            {{ $schools->links() }}
        </div>
    </div>
</div>
@endsection
