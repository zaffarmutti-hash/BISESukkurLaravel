
import { useState } from 'react';
import { Link } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { Paginated } from '@/types';

interface GapStudent {
  id: number;
  full_name: string;
  father_name: string;
  enrollment_number: string;
  class_level: string;
  subject_group: string;
  days_since_enrollment: number;
}

interface ExamGapReportProps {
  students: Paginated<GapStudent>;
  stats: { total: number };
  activeYear?: { label: string } | null;
}

export default function ExamGapReport({ students, stats, activeYear }: ExamGapReportProps) {
  const [search, setSearch] = useState('');
  const meta = students.meta;

  function handleCopyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
  }

  function handleExportPdf() {
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    window.open(route('school.examination.gap-report.pdf') + '?' + params.toString(), '_blank');
  }

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <Link href={route('school.examination.forms')} className="text-gray-500 hover:text-gray-700">Examination Forms</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">Enrollment to Exam Gap Report</span>
        </nav>
      }
    >
      {/* Page Header */}
      <div className="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Enrollment to Exam Gap Report</h1>
          <p className="text-gray-500 mt-1">
            {activeYear?.label ? `${activeYear.label} - ` : ''}{' '}
            <span className="font-semibold text-amber-600">{stats.total}</span> students have enrollment numbers but no exam form yet
          </p>
        </div>
        <button
          type="button"
          className="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors"
          onClick={handleExportPdf}
        >
          <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="7,10 12,15 17,10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Export PDF
        </button>
      </div>

      {/* Filters */}
      <div className="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <div className="flex items-center gap-4 flex-wrap">
          <div className="flex-1 min-w-[240px]">
            <label className="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Search</label>
            <div className="relative">
              <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                type="text"
                placeholder="Search by name or enrollment number..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Srl</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Student</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Enrollment No</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Class</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Group</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Days Since Enrollment</th>
                <th className="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {students.data?.map((student, index) => {
                const serial = ((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? 20) + index + 1;
                return (
                  <tr key={student.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-4 py-3 text-sm text-gray-500">{serial}</td>
                    <td className="px-4 py-3">
                      <div className="font-semibold text-gray-900">{student.full_name}</div>
                      <div className="text-xs text-gray-500">{student.father_name}</div>
                    </td>
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        className="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 font-mono text-sm hover:bg-blue-100 transition-colors"
                        onClick={() => handleCopyToClipboard(student.enrollment_number)}
                        title="Click to copy"
                      >
                        {student.enrollment_number}
                        <svg className="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                        </svg>
                      </button>
                    </td>
                    <td className="px-4 py-3">
                      <span className="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold uppercase">
                        {student.class_level.replace('_', ' ')}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-600 capitalize">{student.subject_group}</td>
                    <td className="px-4 py-3">
                      <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${
                        student.days_since_enrollment > 30
                          ? 'bg-red-50 text-red-700'
                          : student.days_since_enrollment > 14
                          ? 'bg-amber-50 text-amber-700'
                          : 'bg-green-50 text-green-700'
                      }`}>
                        {student.days_since_enrollment} days
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Link
                        href={route('school.examination.form.create', {
                          mode: student.class_level.includes('part2') ? 'returning' : 'new',
                          enrollment_number: student.enrollment_number
                        })}
                        className="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors"
                      >
                        <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                          <line x1="12" y1="5" x2="12" y2="19"/>
                          <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Start Exam Form
                      </Link>
                    </td>
                  </tr>
                );
              })}
              {!students.data?.length && (
                <tr>
                  <td colSpan={7} className="px-4 py-12">
                    <div className="flex flex-col items-center text-center">
                      <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
                        <svg className="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                          <circle cx="9" cy="7" r="4"/>
                          <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                          <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                      </div>
                      <h3 className="text-lg font-semibold text-gray-700 mb-1">No missing students found</h3>
                      <p className="text-gray-500">All enrolled students have started their exam forms!</p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {(meta?.last_page ?? 0) > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <span className="text-sm text-gray-500">
              Showing {meta?.from}–{meta?.to} of {meta?.total} students
            </span>
            <div className="flex items-center gap-1">
              {meta?.links.map((link, idx) => {
                const label = link.label.replace('&laquo;', '«').replace('&raquo;', '»');
                return (
                  <Link
                    key={idx}
                    href={link.url ?? '#'}
                    preserveScroll
                    className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                      link.active
                        ? 'bg-blue-600 text-white'
                        : link.url
                          ? 'text-gray-600 hover:bg-gray-100'
                          : 'text-gray-400 cursor-not-allowed'
                    }`}
                  >
                    {label}
                  </Link>
                );
              })}
            </div>
          </div>
        )}
      </div>
    </SchoolLayout>
  );
}
