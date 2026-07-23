
import { Deferred, Link } from '@inertiajs/react';
import DistrictAdminLayout from '@/layouts/DistrictAdminLayout';

interface School {
  id: number;
  name: string;
  semis_code: string;
  total_students: number;
  exam_form_count: number;
  verified_amount: number;
  pending_verification: number;
  enrollment_open: boolean;
  exam_open: boolean;
}

interface Announcement {
  id: number;
  title: string;
  content: string;
  created_at: string;
  is_unread: boolean;
}

interface DashboardProps {
  district: { name: string };
  total_schools: number;
  active_schools: number;
  total_students: number;
  verified_amount: number;
  pending_verifications: number;
  missing_exam_forms: number;
  school_admin_count: number;
  schools: School[];
  announcements: Announcement[];
}

export default function Dashboard({
  district,
  total_schools,
  active_schools,
  total_students,
  verified_amount,
  pending_verifications,
  missing_exam_forms,
  school_admin_count,
  schools,
  announcements,
}: DashboardProps) {
  return (
    <DistrictAdminLayout breadcrumb={<span className="text-sm text-amber-700">Dashboard</span>}>
      <div className="mb-6">
        <h1 className="text-2xl font-extrabold text-amber-900">{district.name} — District Admin</h1>
        <p className="text-amber-700 mt-1">Monitor and manage schools in your district</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Deferred data="total_schools" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <Link href={route('district.schools')} className="block">
            <div className="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
              <div className="text-3xl font-extrabold">{total_schools}</div>
              <div className="text-amber-100 text-sm font-semibold uppercase tracking-wider mt-1">Total Schools</div>
              <div className="text-amber-200 text-xs mt-2">Active: {active_schools}</div>
            </div>
          </Link>
        </Deferred>

        <Deferred data="total_students" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <div className="bg-gradient-to-br from-amber-600 to-orange-600 rounded-2xl p-5 text-white shadow-lg">
            <div className="text-3xl font-extrabold">{total_students.toLocaleString()}</div>
            <div className="text-orange-100 text-sm font-semibold uppercase tracking-wider mt-1">Total Students</div>
          </div>
        </Deferred>

        <Deferred data="verified_amount" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <div className="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-5 text-white shadow-lg">
            <div className="text-3xl font-extrabold">Rs {verified_amount.toLocaleString()}</div>
            <div className="text-green-100 text-sm font-semibold uppercase tracking-wider mt-1">Verified Amount</div>
          </div>
        </Deferred>

        <Deferred data="pending_verifications" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <div className={`rounded-2xl p-5 text-white shadow-lg ${pending_verifications > 0 ? 'bg-gradient-to-br from-red-500 to-red-600 animate-pulse' : 'bg-gradient-to-br from-gray-500 to-gray-600'}`}>
            <div className="text-3xl font-extrabold">{pending_verifications}</div>
            <div className="text-white/90 text-sm font-semibold uppercase tracking-wider mt-1">Pending Verifications</div>
          </div>
        </Deferred>

        <Deferred data="missing_exam_forms" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <div className={`rounded-2xl p-5 text-white shadow-lg ${missing_exam_forms > 0 ? 'bg-gradient-to-br from-orange-500 to-orange-600 animate-pulse' : 'bg-gradient-to-br from-green-500 to-green-600'}`}>
            <div className="text-3xl font-extrabold">{missing_exam_forms}</div>
            <div className="text-white/90 text-sm font-semibold uppercase tracking-wider mt-1">Missing Exam Forms</div>
          </div>
        </Deferred>

        <Deferred data="school_admin_count" fallback={<div className="h-28 bg-white/70 rounded-2xl border border-amber-200 animate-pulse" />}>
          <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg">
            <div className="text-3xl font-extrabold">{school_admin_count}</div>
            <div className="text-blue-100 text-sm font-semibold uppercase tracking-wider mt-1">School Admins</div>
          </div>
        </Deferred>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <div className="bg-white rounded-2xl shadow-lg border border-amber-200 overflow-hidden">
            <div className="p-5 border-b border-amber-200 flex items-center justify-between">
              <h2 className="text-lg font-bold text-amber-900">Schools Overview</h2>
              <Link href={route('district.schools')} className="text-amber-600 text-sm font-semibold hover:text-amber-800">View All →</Link>
            </div>
            <div className="overflow-x-auto" style={{ maxHeight: '500px' }}>
              <Deferred data="schools" fallback={<div className="p-8 text-center text-amber-600">Loading schools...</div>}>
                <table className="w-full text-sm">
                  <thead className="bg-amber-50 sticky top-0">
                    <tr>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">School</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">SEMIS Code</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">Students</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">Exam Forms</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">Verified</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">Pending</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide">Status</th>
                      <th className="px-5 py-3 text-left text-amber-900 font-semibold uppercase tracking-wide"></th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-amber-100">
                    {schools.map((school) => (
                      <tr key={school.id} className="hover:bg-amber-50/50">
                        <td className="px-5 py-4">
                          <div className="font-semibold text-amber-900">{school.name}</div>
                        </td>
                        <td className="px-5 py-4 text-amber-700 font-mono">{school.semis_code}</td>
                        <td className="px-5 py-4 text-amber-800 font-semibold">{school.total_students.toLocaleString()}</td>
                        <td className="px-5 py-4 text-amber-800 font-semibold">{school.exam_form_count}</td>
                        <td className="px-5 py-4 text-green-700 font-semibold">Rs {school.verified_amount.toLocaleString()}</td>
                        <td className="px-5 py-4">
                          {school.pending_verification > 0 ? (
                            <span className="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">{school.pending_verification}</span>
                          ) : (
                            <span className="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">0</span>
                          )}
                        </td>
                        <td className="px-5 py-4">
                          <div className="flex gap-1">
                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${school.enrollment_open ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                              Enrollment {school.enrollment_open ? 'Open' : 'Closed'}
                            </span>
                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${school.exam_open ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                              Exam {school.exam_open ? 'Open' : 'Closed'}
                            </span>
                          </div>
                        </td>
                        <td className="px-5 py-4">
                          <Link href={route('district.schools.show', { school: school.id })} className="text-amber-600 hover:text-amber-800 font-semibold text-sm">Details</Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </Deferred>
            </div>
          </div>
        </div>

        <div>
          <div className="bg-white rounded-2xl shadow-lg border border-amber-200 overflow-hidden">
            <div className="p-5 border-b border-amber-200 flex items-center justify-between">
              <h2 className="text-lg font-bold text-amber-900">Announcements</h2>
              <Link href={route('district.announcements')} className="text-amber-600 text-sm font-semibold hover:text-amber-800">All →</Link>
            </div>
            <div className="p-4 space-y-3" style={{ maxHeight: '500px', overflowY: 'auto' }}>
              <Deferred data="announcements" fallback={<div className="text-center text-amber-600 p-4">Loading announcements...</div>}>
                {announcements.length > 0 ? (
                  announcements.map((announcement) => (
                    <div key={announcement.id} className={`p-4 rounded-xl border ${announcement.is_unread ? 'bg-amber-50 border-l-4 border-amber-500' : 'bg-white border-l-4 border-gray-200'}`}>
                      <div className="flex items-start justify-between gap-2">
                        <div className="flex-1">
                          <div className="font-semibold text-amber-900">{announcement.title}</div>
                          <div className="text-amber-700 text-sm mt-1 line-clamp-3">{announcement.content}</div>
                          <div className="text-amber-500 text-xs mt-2">{announcement.created_at}</div>
                        </div>
                        {announcement.is_unread && (
                          <div className="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0 mt-1.5"></div>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center text-amber-600 p-4">No announcements yet</div>
                )}
              </Deferred>
            </div>
          </div>
        </div>
      </div>
    </DistrictAdminLayout>
  );
}
