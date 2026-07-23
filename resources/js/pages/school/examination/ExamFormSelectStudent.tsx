
import { Link } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { Student, AcademicYear } from '@/types';

interface ExamFormSelectStudentProps {
  mode: 'new' | 'returning';
  students: Student[];
  activeYear?: AcademicYear | null;
}

export default function ExamFormSelectStudent({ mode, students, activeYear }: ExamFormSelectStudentProps) {
  const title = mode === 'returning' ? 'Add SSC-II / HSC-II Exam Form' : 'Add SSC-I / HSC-I Exam Form';

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <Link href={route('school.examination.forms')} className="text-gray-500 hover:text-gray-700">
            Examination Forms
          </Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">{title}</span>
        </nav>
      }
    >
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
        <p className="text-gray-500 mt-1">
          {activeYear?.label ? `${activeYear.label} — ` : ''}
          Select a student to start their examination form.
        </p>
      </div>

      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Student</th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Enrollment No</th>
                <th className="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Class</th>
                <th className="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {students.map((student) => (
                <tr key={student.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3">
                    <div className="font-semibold text-gray-900">{student.full_name}</div>
                    <div className="text-xs text-gray-500">{student.father_name}</div>
                  </td>
                  <td className="px-4 py-3 font-mono text-sm text-blue-700">
                    {student.enrollment_number ?? 'Pending'}
                  </td>
                  <td className="px-4 py-3 text-sm text-gray-600 capitalize">
                    {student.academic_record?.class_level?.replace('_', ' ') ?? '—'}
                  </td>
                  <td className="px-4 py-3 text-right">
                    <Link
                      href={route('school.examination.form.create', {
                        student_id: student.id,
                        mode,
                      })}
                      className="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold"
                    >
                      Start Exam Form
                    </Link>
                  </td>
                </tr>
              ))}
              {!students.length && (
                <tr>
                  <td colSpan={4} className="px-4 py-12 text-center text-gray-500">
                    No eligible students found for this exam form type.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </SchoolLayout>
  );
}
