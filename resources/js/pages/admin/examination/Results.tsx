import React, { useState, useEffect } from 'react';
import { useForm, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { AppButton } from '@/components/shared/AppButton';
import { AppSelect } from '@/components/shared/AppSelect';
import { usePermissions } from '@/hooks/usePermissions';

interface StudentRow {
  student_id: number;
  exam_form_id: number;
  roll_number: string;
  name: string;
  father_name: string;
  result_id?: number | null;
  marks_obtained: number | string;
  status: string;
}

interface TimetableSlot {
  id: number;
  subject_name: string;
  subject_code: string;
  class_level: string;
  total_marks: number;
}

interface ResultsProps {
  timetables: TimetableSlot[];
  students: StudentRow[];
  timetable?: TimetableSlot | null;
  filters: {
    exam_timetable_id?: string;
  };
}

export default function Results({ timetables, students, timetable, filters: _filters }: ResultsProps) {
  const { can } = usePermissions();
  const [localMarks, setLocalMarks] = useState<Record<number, string>>({});

  useEffect(() => {
    const initialMarks: Record<number, string> = {};
    students.forEach((s) => {
      initialMarks[s.student_id] = String(s.marks_obtained);
    });
    setLocalMarks(initialMarks);
  }, [students]);

  const form = useForm({
    exam_timetable_id: String(timetable?.id ?? ''),
    results: [] as { student_id: number; exam_form_id: number; marks_obtained: string }[],
  });

  function selectSubject(e: React.ChangeEvent<HTMLSelectElement>) {
    const val = e.target.value;
    router.get(route('superadmin.examination.results.index'), { exam_timetable_id: val });
  }

  function handleMarkChange(studentId: number, val: string) {
    setLocalMarks((prev) => ({
      ...prev,
      [studentId]: val,
    }));
  }

  function saveMarks() {
    const resultsData = students.map((s) => ({
      student_id: s.student_id,
      exam_form_id: s.exam_form_id,
      marks_obtained: localMarks[s.student_id] ?? '',
    }));

    form.setData({
      exam_timetable_id: String(timetable?.id ?? ''),
      results: resultsData,
    });

    // We can directly submit using router post
    router.post(route('superadmin.examination.results.store'), {
      exam_timetable_id: timetable?.id,
      results: resultsData,
    });
  }

  function verifyResult(resultId: number) {
    router.post(route('superadmin.examination.results.verify', resultId));
  }

  const classNames: Record<string, string> = {
    ssc_part1: 'SSC Part I (9th)',
    ssc_part2: 'SSC Part II (10th)',
    hsc_part1: 'HSC Part I (11th)',
    hsc_part2: 'HSC Part II (12th)',
  };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Result Entry</span>}>
      <PageHeader
        title="Candidate Result Entry"
        subtitle="Record subject-wise marks, auto-compute candidate grades, and verify entries"
      />

      <div className="card mb-6">
        <div className="card-body grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
          <AppSelect
            label="Select Exam Subject & Class Level"
            value={String(timetable?.id ?? '')}
            onChange={selectSubject}
            placeholder="Select a subject..."
          >
            {timetables.map((t) => (
              <option key={t.id} value={t.id}>
                {t.subject_code} - {t.subject_name} ({classNames[t.class_level] || t.class_level})
              </option>
            ))}
          </AppSelect>
          {timetable && (
            <div className="text-sm text-slate-500 bg-slate-50 border border-slate-100 rounded-lg p-3">
              Total Marks: <strong className="text-slate-800">{timetable.total_marks}</strong> | Passing Marks: <strong className="text-slate-800">{Math.round(timetable.total_marks * 0.33)} (33%)</strong>
            </div>
          )}
        </div>
      </div>

      {timetable ? (
        <div className="card">
          <div className="card-header flex items-center justify-between">
            <h2 className="card-title">Enrolled Candidates ({students.length})</h2>
            {can('examination.view') && students.length > 0 && (
              <AppButton variant="primary" size="sm" onClick={saveMarks}>
                Save All Marks
              </AppButton>
            )}
          </div>
          <div className="card-body overflow-x-auto p-0">
            <table className="app-table w-full">
              <thead>
                <tr>
                  <th className="pl-6">Roll Number</th>
                  <th>Candidate Name</th>
                  <th>Father Name</th>
                  <th className="w-[180px]">Marks Obtained (Max {timetable.total_marks})</th>
                  <th>Status</th>
                  <th className="pr-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {students.map((s) => {
                  const marks = localMarks[s.student_id] ?? '';
                  const isExceeded = Number(marks) > timetable.total_marks;

                  return (
                    <tr key={s.student_id}>
                      <td className="pl-6 font-mono font-semibold text-slate-800">{s.roll_number}</td>
                      <td className="font-semibold text-slate-900">{s.name}</td>
                      <td>{s.father_name}</td>
                      <td>
                        <input
                          type="number"
                          min="0"
                          max={timetable.total_marks}
                          value={marks}
                          onChange={(e) => handleMarkChange(s.student_id, e.target.value)}
                          disabled={s.status === 'verified'}
                          className={`form-input w-[120px] font-bold text-center ${isExceeded ? 'border-red-500 bg-red-50 text-red-900' : ''}`}
                        />
                        {isExceeded && <div className="text-[10px] text-red-600 mt-1">Exceeds max!</div>}
                      </td>
                      <td>
                        <StatusBadge
                          label={s.status === 'verified' ? 'Verified' : (s.status === 'entered' ? 'Entered' : 'Pending Marks')}
                          variant={s.status === 'verified' ? 'success' : (s.status === 'entered' ? 'warning' : 'neutral')}
                        />
                      </td>
                      <td className="pr-6 text-right whitespace-nowrap">
                        {s.status === 'entered' && can('examination.view') && s.result_id && (
                          <AppButton
                            variant="secondary"
                            size="sm"
                            onClick={() => verifyResult(s.result_id!)}
                          >
                            Verify ✓
                          </AppButton>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="alert alert-info text-center py-6">
          Select an exam slot from the dropdown list to perform marks entry.
        </div>
      )}
    </SuperAdminLayout>
  );
}
