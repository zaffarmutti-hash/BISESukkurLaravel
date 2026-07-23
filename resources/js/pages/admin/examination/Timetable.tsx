import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { AppButton } from '@/components/shared/AppButton';
import { AppModal } from '@/components/shared/AppModal';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { AcademicYear, ExamTimetable, ExamCenter } from '@/types';

interface TimetableProps {
  timetables: (ExamTimetable & { exam_center?: ExamCenter })[];
  centers: ExamCenter[];
  activeYear?: AcademicYear | null;
  filters: {
    class_level?: string;
    search?: string;
  };
}

export default function Timetable({ timetables, centers, activeYear, filters }: TimetableProps) {
  const { can } = usePermissions();
  const [showModal, setShowModal] = useState(false);
  const [editingSlot, setEditingSlot] = useState<ExamTimetable | null>(null);
  const [deletingSlot, setDeletingSlot] = useState<ExamTimetable | null>(null);

  const form = useForm({
    exam_center_id: '',
    class_level: 'ssc_part1',
    subject_name: '',
    subject_code: '',
    exam_date: '',
    start_time: '09:00',
    end_time: '12:00',
    total_marks: '100',
  });

  function openCreate() {
    setEditingSlot(null);
    form.setData({
      exam_center_id: centers[0]?.id ? String(centers[0].id) : '',
      class_level: filters.class_level ?? 'ssc_part1',
      subject_name: '',
      subject_code: '',
      exam_date: '',
      start_time: '09:00',
      end_time: '12:00',
      total_marks: '100',
    });
    form.clearErrors();
    setShowModal(true);
  }

  function openEdit(slot: ExamTimetable) {
    setEditingSlot(slot);
    form.setData({
      exam_center_id: String(slot.exam_center_id),
      class_level: slot.class_level,
      subject_name: slot.subject_name,
      subject_code: slot.subject_code,
      exam_date: slot.exam_date ? new Date(slot.exam_date).toISOString().split('T')[0] : '',
      start_time: slot.start_time.substring(0, 5),
      end_time: slot.end_time.substring(0, 5),
      total_marks: String(slot.total_marks),
    });
    form.clearErrors();
    setShowModal(true);
  }

  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.examination.timetable.index'), Object.fromEntries(formData), { preserveState: true });
  }

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (editingSlot) {
      form.put(route('superadmin.examination.timetable.update', editingSlot.id), {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        }
      });
    } else {
      form.post(route('superadmin.examination.timetable.store'), {
        onSuccess: () => {
          setShowModal(false);
          form.reset();
        }
      });
    }
  }

  function handleDelete() {
    if (!deletingSlot) return;
    router.delete(route('superadmin.examination.timetable.destroy', deletingSlot.id), {
      onSuccess: () => setDeletingSlot(null),
    });
  }

  const classNames: Record<string, string> = {
    ssc_part1: 'SSC Part I (9th)',
    ssc_part2: 'SSC Part II (10th)',
    hsc_part1: 'HSC Part I (11th)',
    hsc_part2: 'HSC Part II (12th)',
  };

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Timetable</span>}>
      <PageHeader
        title="Official Exam Timetable"
        subtitle="Schedule examinations slots, dates, durations, and subject groups"
        actions={
          activeYear && can('examination.view') && (
            <AppButton variant="primary" onClick={openCreate}>
              + Schedule Exam Slot
            </AppButton>
          )
        }
      />

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200">
        <div className="card-body grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <AppSelect
            label="Class Level"
            name="class_level"
            defaultValue={filters.class_level ?? ''}
            placeholder=""
          >
            <option value="">All Class Levels</option>
            <option value="ssc_part1">SSC Part I (9th)</option>
            <option value="ssc_part2">SSC Part II (10th)</option>
            <option value="hsc_part1">HSC Part I (11th)</option>
            <option value="hsc_part2">HSC Part II (12th)</option>
          </AppSelect>
          <AppInput
            label="Search Subject / Code"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Search subject code or name..."
          />
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Filter Schedule
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Exam Date</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Class Level</th>
                <th>Time & Duration</th>
                <th>Center Assignment</th>
                <th>Total Marks</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {timetables.map((t) => (
                <tr key={t.id}>
                  <td className="pl-6 font-semibold text-slate-800">
                    {t.exam_date ? new Date(t.exam_date).toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : '—'}
                  </td>
                  <td className="font-mono text-xs font-semibold text-slate-600">{t.subject_code}</td>
                  <td className="font-medium text-slate-900">{t.subject_name}</td>
                  <td>
                    <span className="badge badge-neutral text-xs">
                      {classNames[t.class_level] || t.class_level.replace(/_/g, ' ').toUpperCase()}
                    </span>
                  </td>
                  <td>
                    <div className="text-slate-800 font-semibold">{t.start_time.substring(0,5)} - {t.end_time.substring(0,5)}</div>
                    <div className="text-xs text-slate-400">Morning/Evening slot</div>
                  </td>
                  <td className="text-xs font-medium text-slate-600">{t.exam_center?.name}</td>
                  <td className="font-bold text-slate-950">{t.total_marks}</td>
                  <td className="pr-6 text-right whitespace-nowrap space-x-2">
                    <AppButton variant="ghost" size="sm" onClick={() => openEdit(t)}>
                      Edit
                    </AppButton>
                    <AppButton variant="ghost" className="text-red-600 hover:bg-red-50" size="sm" onClick={() => setDeletingSlot(t)}>
                      Delete
                    </AppButton>
                  </td>
                </tr>
              ))}
              {timetables.length === 0 && (
                <tr>
                  <td colSpan={8} className="text-center py-8 text-slate-400 text-sm">
                    No scheduled exam slots found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Create / Edit Modal */}
      <AppModal open={showModal} onClose={() => setShowModal(false)} title={editingSlot ? 'Edit Scheduled Exam' : 'Schedule Exam Slot'}>
        <form onSubmit={onSubmit} className="px-6 py-4 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <AppSelect
              label="Class Level"
              value={form.data.class_level}
              onChange={(e) => form.setData('class_level', e.target.value)}
              required
            >
              <option value="ssc_part1">SSC Part I (9th)</option>
              <option value="ssc_part2">SSC Part II (10th)</option>
              <option value="hsc_part1">HSC Part I (11th)</option>
              <option value="hsc_part2">HSC Part II (12th)</option>
            </AppSelect>

            <AppSelect
              label="Exam Center"
              value={form.data.exam_center_id}
              onChange={(e) => form.setData('exam_center_id', e.target.value)}
              required
            >
              {centers.map((c) => (
                <option key={c.id} value={c.id}>{c.name} ({c.code})</option>
              ))}
            </AppSelect>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <AppInput
              label="Subject Code"
              placeholder="e.g. ENG-01"
              value={form.data.subject_code}
              onChange={(e) => form.setData('subject_code', e.target.value)}
              className="col-span-1"
              required
            />
            <AppInput
              label="Subject Name"
              placeholder="e.g. English Compulsory"
              value={form.data.subject_name}
              onChange={(e) => form.setData('subject_name', e.target.value)}
              className="col-span-2"
              required
            />
          </div>

          <div className="grid grid-cols-3 gap-4">
            <AppInput
              label="Exam Date"
              type="date"
              value={form.data.exam_date}
              onChange={(e) => form.setData('exam_date', e.target.value)}
              required
            />
            <AppInput
              label="Start Time"
              type="time"
              value={form.data.start_time}
              onChange={(e) => form.setData('start_time', e.target.value)}
              required
            />
            <AppInput
              label="End Time"
              type="time"
              value={form.data.end_time}
              onChange={(e) => form.setData('end_time', e.target.value)}
              required
            />
          </div>

          <AppInput
            label="Total Exam Marks"
            type="number"
            min="1"
            value={form.data.total_marks}
            onChange={(e) => form.setData('total_marks', e.target.value)}
            required
          />

          {Object.keys(form.errors).length > 0 && (
            <div className="bg-red-50 text-red-700 text-xs p-3 rounded border border-red-200">
              {Object.values(form.errors).map((err, idx) => (
                <div key={idx}>{err}</div>
              ))}
            </div>
          )}

          <div className="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <AppButton variant="secondary" onClick={() => setShowModal(false)} disabled={form.processing}>
              Cancel
            </AppButton>
            <AppButton type="submit" variant="primary" loading={form.processing}>
              {editingSlot ? 'Save Changes' : 'Schedule Slot'}
            </AppButton>
          </div>
        </form>
      </AppModal>

      {/* Delete Confirmation */}
      <ConfirmDialog
        open={!!deletingSlot}
        title="Cancel Exam Slot"
        variant="danger"
        message={`Are you sure you want to cancel exam slot "${deletingSlot?.subject_name}" on ${deletingSlot?.exam_date}?`}
        onConfirm={handleDelete}
        onCancel={() => setDeletingSlot(null)}
      />
    </SuperAdminLayout>
  );
}
