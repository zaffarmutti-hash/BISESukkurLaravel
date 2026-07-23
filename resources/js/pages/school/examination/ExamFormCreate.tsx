
import { useForm, Link } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { Student, ExamForm } from '@/types';

interface ExamFormCreateProps {
  student:    Student;
  examForm?:   ExamForm | null;
  isLocked?:   boolean;
  viewOnly?:   boolean;
}

export default function ExamFormCreate({
  student,
  examForm,
  isLocked = false,
  viewOnly = false,
}: ExamFormCreateProps) {
  const isEditing = !!examForm;
  const readOnly  = viewOnly || isLocked;

  const { data, setData, post, put, processing, errors, transform } = useForm({
    student_id:                 student.id,
    student_academic_record_id: student.academic_record?.id ?? '',
    exam_center_id:             examForm?.exam_center_id ?? '',
    save_as:                    'draft',
  });

  function submit(saveAs: 'draft' | 'final') {
    transform((data) => ({
      ...data,
      save_as: saveAs,
    }));

    if (isEditing && examForm) {
      put(route('school.examination.form.update', examForm.id), { preserveScroll: true });
    } else {
      post(route('school.examination.form.store'), { preserveScroll: true });
    }
  }

  function handleExportPdf() {
    if (examForm) {
      window.open(route('school.documents.exam-form.pdf', examForm.id), '_blank');
    }
  }

  const pageTitle = viewOnly
    ? 'View Examination Form'
    : isEditing
      ? 'Edit Examination Form'
      : 'Fill Examination Form';

  const currentStatus = examForm?.status ?? 'draft';

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <Link href={route('school.examination.forms')} className="text-gray-500 hover:text-gray-700">Exam Forms</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">{pageTitle}</span>
        </nav>
      }
    >
      <div className="efc-page-header">
        <div>
          <h1 className="efc-page-title">{pageTitle}</h1>
          <p className="efc-page-subtitle">Student: <strong>{student.full_name}</strong></p>
        </div>
        <div className="efc-header-actions">
          {isEditing && (
            <button type="button" className="efc-btn efc-btn-outline" onClick={handleExportPdf}>
              Export PDF Slip
            </button>
          )}
          {examForm && (
            <span className={`efc-status-badge efc-status-${currentStatus}`}>
              {currentStatus.toUpperCase()}
            </span>
          )}
        </div>
      </div>

      {isLocked && (
        <div className="efc-locked-banner">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
          </svg>
          This examination form is locked and cannot be modified.
        </div>
      )}

      <div className="efc-form-card">
        <form onSubmit={(e) => e.preventDefault()}>
          <div className="efc-section">
            <h2 className="efc-section-title">Student Details</h2>
            <div className="efc-details-grid">
              <div>
                <span className="efc-details-label">Full Name</span>
                <span className="efc-details-val">{student.full_name}</span>
              </div>
              <div>
                <span className="efc-details-label">Father's Name</span>
                <span className="efc-details-val">{student.father_name}</span>
              </div>
              <div>
                <span className="efc-details-label">Enrollment #</span>
                <span className="efc-details-val efc-mono">{student.enrollment_number ?? 'Pending'}</span>
              </div>
              <div>
                <span className="efc-details-label">Class / Group</span>
                <span className="efc-details-val">
                  {student.academic_record?.class_level?.replace('_', ' ').toUpperCase()} ({student.academic_record?.subject_group})
                </span>
              </div>
            </div>
          </div>

          <div className="efc-section">
            <h2 className="efc-section-title">Examination Settings</h2>
            <div className="efc-form-grid">
              <div className="efc-field">
                <label className="efc-label">Select Exam Center (Optional)</label>
                <select
                  className="efc-select"
                  value={data.exam_center_id}
                  onChange={(e) => setData('exam_center_id', e.target.value)}
                  disabled={readOnly}
                >
                  <option value="">— Automatically Assign Center —</option>
                  {/* Exam centers will be loaded here dynamically in future phases */}
                </select>
                {errors.exam_center_id && <p className="efc-error">{errors.exam_center_id}</p>}
              </div>
            </div>
          </div>

          {!readOnly && (
            <div className="efc-footer">
              <Link href={route('school.examination.forms')} className="efc-btn efc-btn-ghost">Cancel</Link>
              <div className="efc-footer-right">
                <button type="button" className="efc-btn efc-btn-outline" disabled={processing} onClick={() => submit('draft')}>
                  Save as Draft
                </button>
                <button type="button" className="efc-btn efc-btn-navy" disabled={processing} onClick={() => submit('final')}>
                  Confirm Final
                </button>
              </div>
            </div>
          )}
        </form>
      </div>

      <style>{`
        .efc-page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
        .efc-page-title{font-size:1.375rem;font-weight:800;color:#111827}
        .efc-page-subtitle{font-size:.875rem;color:#6B7280;margin-top:.25rem}
        .efc-header-actions{display:flex;align-items:center;gap:.75rem}
        .efc-status-badge{display:inline-flex;padding:.25rem .75rem;border-radius:9999px;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
        .efc-status-draft{background:#F3F4F6;color:#6B7280}
        .efc-status-final{background:#DBEAFE;color:#1E40AF}
        .efc-status-submitted{background:#FFFBEB;color:#92400E}
        .efc-status-confirmed{background:#D1FAE5;color:#065F46}
        .efc-status-rejected{background:#FEE2E2;color:#991B1B}
        .efc-btn{display:inline-flex;align-items:center;gap:.375rem;padding:.5625rem 1.125rem;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;transition:all .15s ease;border:none;text-decoration:none;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .efc-btn-navy{background:#1B3A6B;color:#fff}.efc-btn-navy:hover:not(:disabled){background:#162d54}
        .efc-btn-ghost{background:transparent;color:#6B7280;border:1.5px solid #E5E7EB}.efc-btn-ghost:hover{background:#F3F4F6}
        .efc-btn-outline{background:#fff;color:#374151;border:1.5px solid #D1D5DB}.efc-btn-outline:hover{background:#F9FAFB}
        .efc-locked-banner{display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:#FEFCE8;color:#92400E;border:1px solid #FDE68A;border-radius:10px;font-size:.875rem;font-weight:500;margin-bottom:1rem}
        .efc-locked-banner svg{width:18px;height:18px;flex-shrink:0}
        .efc-form-card{background:#fff;border:1px solid #E5E7EB;border-radius:16px;overflow:hidden}
        .efc-section{padding:1.5rem 1.75rem;border-bottom:1px solid #F3F4F6}
        .efc-section:last-of-type{border-bottom:none}
        .efc-section-title{font-size:.9375rem;font-weight:700;color:#111827;margin-bottom:1.25rem}
        .efc-details-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
        .efc-details-label{display:block;font-size:.75rem;color:#9CA3AF;text-transform:uppercase;font-weight:600;margin-bottom:.25rem}
        .efc-details-val{font-size:.875rem;font-weight:600;color:#374151}
        .efc-mono{font-family:'Courier New',monospace}
        .efc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .efc-field{display:flex;flex-direction:column;gap:.3rem}
        .efc-label{font-size:.8125rem;font-weight:600;color:#374151}
        .efc-select{width:100%;padding:.5625rem .875rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;color:#111827;background:#fff;outline:none}
        .efc-select:disabled{background:#F9FAFB;color:#6B7280;cursor:not-allowed}
        .efc-error{font-size:.75rem;color:#EF4444;font-weight:500}
        .efc-footer{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.75rem;border-top:1px solid #E5E7EB;background:#FAFAFA}
        .efc-footer-right{display:flex;gap:.75rem}
        @media(max-width:768px){.efc-details-grid{grid-template-columns:1fr 1fr}.efc-form-grid{grid-template-columns:1fr}}
      `}</style>
    </SchoolLayout>
  );
}
