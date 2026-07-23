import React, { useState, useEffect } from 'react';
import { router, usePage } from '@inertiajs/react';
import { createPortal } from 'react-dom';

// ─── Types ────────────────────────────────────────────────────────────────────

interface FetchedStudent {
  id: number;
  full_name: string;
  father_name: string;
  cnic_or_bform?: string | null;
  cnic?: string | null;
  fee_amount?: number;
}

interface ChallanForm {
  class_level:     string;
  group:           string;
  student_type:    string;
}

type Step = 1 | 2 | 3 | 'loading' | 'success';

interface GenerateChallanDialogProps {
  show:        boolean;
  selectedIds?: number[];
  onClose:     () => void;
  onSuccess:   (data?: unknown) => void;
  invoiceType?: 'enrollment' | 'examination';
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function classLabel(classLevel: string): string {
  const map: Record<string, string> = {
    ssc_part1: 'SSC Part 1',
    ssc_part2: 'SSC Part 2',
    hsc_part1: 'HSC Part 1',
    hsc_part2: 'HSC Part 2',
    matric:        'Matric (SSC)',
    intermediate:  'Intermediate (HSC)',
  };
  return map[classLevel] ?? classLevel;
}

function studentTypeLabel(type: string): string {
  const map: Record<string, string> = {
    fresh:    'Fresh',
    repeater: 'Repeater',
    private:  'Private',
  };
  return map[type] ?? type;
}

function getCsrfToken(): string {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

// ─── Component ────────────────────────────────────────────────────────────────

/**
 * Multi-step challan generation dialog.
 * React replacement for GenerateChallanDialog.vue (1054 lines → clean React).
 * Uses createPortal instead of Vue Teleport.
 */
export function GenerateChallanDialog({
  show,
  selectedIds = [],
  onClose,
  onSuccess,
  invoiceType = 'enrollment',
}: GenerateChallanDialogProps) {
  const { props } = usePage();
  const activeYear = (props as any).activeYear;
  const currentPhaseResult = invoiceType === 'examination'
    ? activeYear?.examination_phase
    : activeYear?.enrollment_phase;
  const isGrace = currentPhaseResult?.phase === 'grace';

  const [step,              setStep]              = useState<Step>(1);
  const [loadingMsg,        setLoadingMsg]        = useState('Fetching students…');
  const [step1Error,        setStep1Error]        = useState('');
  const [generateError,     setGenerateError]     = useState('');
  const [fetchedStudents,   setFetchedStudents]   = useState<FetchedStudent[]>([]);
  const [selectedStudents,  setSelectedStudents]  = useState<number[]>([]);
  const [invoiceNo,         setInvoiceNo]         = useState('');
  const [challanId,         setChallanId]         = useState<number | null>(null);

  const [form, setForm] = useState<ChallanForm>({
    class_level:  '',
    group:        '',
    student_type: '',
  });

  // ─── Derived state ────────────────────────────────────────────────────────

  const stepNumber  = step === 'loading' || step === 'success' ? 3 : (step as number);
  const canFetch    = !!(form.class_level && form.group);
  const allSelected = fetchedStudents.length > 0 && selectedStudents.length === fetchedStudents.length;

  const selectedStudentRecords = fetchedStudents.filter((s) => selectedStudents.includes(s.id));
  const totalAmount = selectedStudentRecords.reduce((sum, s) => sum + (s.fee_amount ?? 0), 0);
  const boardFeePerStudent = fetchedStudents[0]?.fee_amount ?? null;

  const modalTitle: Record<Step, string> = {
    1:       'Generate Challan — Select Criteria',
    2:       'Generate Challan — Select Students',
    3:       'Generate Challan — Confirm',
    loading: 'Please Wait…',
    success: 'Challan Generated Successfully',
  };

  // ─── Keyboard handler ─────────────────────────────────────────────────────

  useEffect(() => {
    function onKeydown(e: KeyboardEvent) {
      if (e.key === 'Escape' && show && step !== 'loading') onClose();
    }
    document.addEventListener('keydown', onKeydown);
    return () => document.removeEventListener('keydown', onKeydown);
  }, [show, step, onClose]);

  // ─── Reset on close ───────────────────────────────────────────────────────

  useEffect(() => {
    if (!show) {
      const timer = setTimeout(() => {
        setStep(1);
        setStep1Error('');
        setFetchedStudents([]);
        setSelectedStudents([]);
        setInvoiceNo('');
        setChallanId(null);
        setGenerateError('');
        setForm({ class_level: '', group: '', student_type: '' });
      }, 300);
      return () => clearTimeout(timer);
    }
  }, [show]);

  // ─── Actions ──────────────────────────────────────────────────────────────

  function toggleStudent(id: number) {
    setSelectedStudents((prev) =>
      prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
    );
  }

  function toggleSelectAll() {
    setSelectedStudents(allSelected ? [] : fetchedStudents.map((s) => s.id));
  }

  async function fetchStudents() {
    setStep1Error('');
    if (!canFetch) {
      setStep1Error('Please select Class Level and Group.');
      return;
    }

    setStep('loading');
    setLoadingMsg('Fetching eligible students…');

    try {
      const params = new URLSearchParams({
        class_level:  form.class_level,
        group:        form.group,
        status:       'challan_eligible',
        invoice_type: invoiceType,
      });
      if (form.student_type) params.set('student_type', form.student_type);

      const res = await fetch(`${route('school.students.index')}?${params.toString()}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });

      if (!res.ok) {
        const err = (await res.json().catch(() => ({}))) as { message?: string };
        throw new Error(err.message ?? 'Failed to fetch students');
      }

      const data = (await res.json()) as { data?: FetchedStudent[] } | FetchedStudent[];
      const students: FetchedStudent[] = Array.isArray(data) ? data : (data.data ?? []);

      if (students.length === 0) {
        setStep1Error('No eligible students found for the selected criteria.');
        setStep(1);
        return;
      }

      setFetchedStudents(students);
      const preselected = selectedIds.length > 0
        ? students.filter((s) => selectedIds.includes(s.id)).map((s) => s.id)
        : students.map((s) => s.id);
      setSelectedStudents(preselected);
      setStep(2);
    } catch (err) {
      setStep1Error(err instanceof Error ? err.message : 'Failed to fetch students.');
      setStep(1);
    }
  }

  async function generateChallan() {
    setGenerateError('');
    setStep('loading');
    setLoadingMsg('Generating challan…');

    try {
      const storeRoute = invoiceType === 'examination'
        ? route('school.examination.challan.store')
        : route('school.enrollment.challan.store');

      const res = await fetch(storeRoute, {
        method: 'POST',
        headers: {
          'Content-Type':     'application/json',
          Accept:             'application/json',
          'X-CSRF-TOKEN':     getCsrfToken(),
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          class_level:  form.class_level,
          group:        form.group,
          student_type: form.student_type || 'fresh',
          student_ids:  selectedStudents,
        }),
      });

      if (!res.ok) {
        const err = (await res.json().catch(() => ({}))) as { message?: string };
        throw new Error(err.message ?? 'Failed to generate challan');
      }

      const data = (await res.json()) as { id?: number; invoice_no?: string; challan_no?: string };
      setInvoiceNo(data.invoice_no ?? data.challan_no ?? `CH-${Date.now()}`);
      setChallanId(data.id ?? null);
      setStep('success');
      onSuccess(data);
    } catch (err) {
      setGenerateError(err instanceof Error ? err.message : 'Failed to generate challan.');
      setStep(3);
    }
  }

  function downloadChallanPdf() {
    if (challanId) {
      window.open(route('school.documents.challan.print', challanId), '_blank');
    } else {
      alert('Challan PDF will be available once the backend endpoint is configured.');
    }
  }

  function downloadListPdf() {
    if (challanId) {
      window.open(route('school.documents.challan.students', challanId), '_blank');
    } else {
      alert('Student list PDF will be available once the backend endpoint is configured.');
    }
  }

  function closeAndRefresh() {
    onClose();
    router.reload({ only: ['students'] });
  }

  function handleBackdropClick(e: React.MouseEvent) {
    if (e.target === e.currentTarget && step !== 'loading') onClose();
  }

  // ─── Render ───────────────────────────────────────────────────────────────

  if (!show) return null;

  const dialog = (
    <div className="gcd-backdrop" onMouseDown={handleBackdropClick}>
      <div className="gcd-modal" role="dialog" aria-modal="true" aria-labelledby="gcd-title">

        {/* ── Header ─────────────────────────────────────────────────── */}
        <div className="gcd-header">
          <div className="gcd-header-left">
            <div className="gcd-steps">
              {[1, 2, 3].map((n) => (
                <div
                  key={n}
                  className={`gcd-step-dot${
                    step === 'success'
                      ? ' gcd-step-done'
                      : stepNumber >= n
                      ? ' gcd-step-active'
                      : ''
                  }`}
                />
              ))}
            </div>
            <h2 id="gcd-title" className="gcd-title">{modalTitle[step]}</h2>
          </div>
          {step !== 'loading' && (
            <button type="button" className="gcd-close" onClick={onClose} aria-label="Close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          )}
        </div>

        {/* ── Body ───────────────────────────────────────────────────── */}
        <div className="gcd-body">

          {/* Step 1: Select Criteria */}
          {step === 1 && (
            <div className="gcd-step-content">
              <p className="gcd-step-desc">
                Select the criteria to fetch eligible students for challan generation.
              </p>
              <div className="gcd-form-grid">
                <div className="gcd-form-group">
                  <label className="gcd-label">Class Level <span className="gcd-required">*</span></label>
                  <select
                    className="gcd-select"
                    value={form.class_level}
                    onChange={(e) => setForm((f) => ({ ...f, class_level: e.target.value }))}
                  >
                    <option value="">— Select Class —</option>
                    <option value="ssc_part1">SSC Part 1</option>
                    <option value="ssc_part2">SSC Part 2</option>
                    <option value="hsc_part1">HSC Part 1</option>
                    <option value="hsc_part2">HSC Part 2</option>
                  </select>
                </div>

                <div className="gcd-form-group">
                  <label className="gcd-label">Group <span className="gcd-required">*</span></label>
                  <select
                    className="gcd-select"
                    value={form.group}
                    onChange={(e) => setForm((f) => ({ ...f, group: e.target.value }))}
                  >
                    <option value="">— Select Group —</option>
                    <option value="science">Science</option>
                    <option value="arts">Arts</option>
                    <option value="commerce">Commerce</option>
                  </select>
                </div>

                <div className="gcd-form-group">
                  <label className="gcd-label">Student Type</label>
                  <select
                    className="gcd-select"
                    value={form.student_type}
                    onChange={(e) => setForm((f) => ({ ...f, student_type: e.target.value }))}
                  >
                    <option value="">All Types</option>
                    <option value="fresh">Fresh</option>
                    <option value="repeater">Repeater</option>
                    <option value="private">Private</option>
                  </select>
                </div>

                <div className="gcd-form-group">
                  <label className="gcd-label">Board Fee (per student)</label>
                  <div className="gcd-input-wrap">
                    <span className="gcd-input-prefix">Rs.</span>
                    <input
                      type="text"
                      readOnly
                      className="gcd-input gcd-input-prefixed gcd-input-readonly"
                      value={boardFeePerStudent != null ? boardFeePerStudent.toLocaleString() : 'Set by board policy'}
                    />
                  </div>
                  <p className="gcd-field-hint">Fee is resolved from board policy when students are fetched.</p>
                </div>
              </div>

              {step1Error && (
                <div className="gcd-error-banner">
                  <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  {step1Error}
                </div>
              )}
            </div>
          )}

          {/* Step 2: Student List */}
          {step === 2 && (
            <div className="gcd-step-content">
              <div className="gcd-summary-bar">
                <div className="gcd-summary-stat">
                  <span className="gcd-summary-num">{selectedStudents.length}</span>
                  <span className="gcd-summary-label">of {fetchedStudents.length} selected</span>
                </div>
                <div className="gcd-summary-stat">
                  <span className="gcd-summary-num" style={{ color: '#d97706' }}>Rs. {totalAmount.toLocaleString()}</span>
                  <span className="gcd-summary-label">Total Amount</span>
                </div>
                <button type="button" className="gcd-select-all-btn" onClick={toggleSelectAll}>
                  {allSelected ? 'Deselect All' : 'Select All'}
                </button>
              </div>

              <div className="gcd-student-list">
                {fetchedStudents.map((student) => (
                  <div
                    key={student.id}
                    className={`gcd-student-row${selectedStudents.includes(student.id) ? ' gcd-student-selected' : ''}`}
                    onClick={() => toggleStudent(student.id)}
                  >
                    <div className="gcd-student-info">
                      <div className="gcd-student-name">{student.full_name}</div>
                      <div className="gcd-student-meta">
                        {student.father_name} · {student.cnic_or_bform ?? student.cnic ?? '—'}
                        {student.fee_amount != null && (
                          <> · Rs. {student.fee_amount.toLocaleString()}</>
                        )}
                      </div>
                    </div>
                    <div className="gcd-student-toggle">
                      <div className={`gcd-toggle${selectedStudents.includes(student.id) ? ' gcd-toggle-on' : ''}`}>
                        <div className="gcd-toggle-thumb" />
                      </div>
                    </div>
                  </div>
                ))}

                {fetchedStudents.length === 0 && (
                  <div className="gcd-no-students">
                    <svg className="w-10 h-10 mx-auto mb-2" style={{ color: '#D1D5DB' }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    </svg>
                    <p className="text-sm" style={{ color: '#9CA3AF' }}>No eligible students found for the selected criteria.</p>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Step 3: Confirmation */}
          {step === 3 && (
            <div className="gcd-step-content">
              <div className="gcd-confirm-summary">
                <h3 className="gcd-confirm-title">Challan Summary</h3>
                <div className="gcd-confirm-rows">
                  {[
                    { key: 'Class',           val: classLabel(form.class_level) },
                    { key: 'Group',           val: form.group || '—', capitalize: true },
                    ...(form.student_type ? [{ key: 'Student Type', val: studentTypeLabel(form.student_type), capitalize: false }] : []),
                    { key: 'Students',        val: String(selectedStudents.length) },
                    { key: 'Board fee (avg)', val: boardFeePerStudent != null ? `Rs. ${boardFeePerStudent.toLocaleString()}` : 'Per board policy' },
                  ].map(({ key, val, capitalize }) => (
                    <div key={key} className="gcd-confirm-row">
                      <span className="gcd-confirm-key">{key}</span>
                      <span className={`gcd-confirm-val${capitalize ? ' capitalize' : ''}`}>{val}</span>
                    </div>
                  ))}
                  <div className="gcd-confirm-row gcd-confirm-total">
                    <span className="gcd-confirm-key">Total Amount</span>
                    <span className="gcd-confirm-val" style={{ color: '#15803d' }}>Rs. {totalAmount.toLocaleString()}</span>
                  </div>
                </div>
              </div>

              {generateError && (
                <div className="gcd-error-banner">
                  <svg className="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  {generateError}
                </div>
              )}

              {isGrace && (
                <div className="gcd-confirm-notice" style={{ background: '#FFF7ED', borderColor: '#FED7AA', color: '#9A3412', display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <svg className="w-4 h-4 flex-shrink-0" style={{ color: '#EA580C' }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  <span className="text-sm">
                    <strong>Late Fee Surcharge Active:</strong> The total amount includes a late fee surcharge because this generation is within the grace period.
                  </span>
                </div>
              )}

              <div className="gcd-confirm-notice">
                <svg className="w-4 h-4 flex-shrink-0" style={{ color: '#d97706' }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                  <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span className="text-sm" style={{ color: '#92400e' }}>
                  This action will generate an official challan and cannot be undone. Ensure all details are correct.
                </span>
              </div>
            </div>
          )}

          {/* Loading */}
          {step === 'loading' && (
            <div className="gcd-loading">
              <div className="gcd-spinner" />
              <p className="gcd-loading-text">{loadingMsg}</p>
            </div>
          )}

          {/* Success */}
          {step === 'success' && (
            <div className="gcd-success">
              <div className="gcd-success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
              </div>
              <h3 className="gcd-success-title">Challan Generated!</h3>
              <p className="gcd-success-subtitle">
                Invoice No: <strong className="gcd-invoice-no">{invoiceNo}</strong>
              </p>
              <div className="gcd-download-buttons">
                <button type="button" className="gcd-download-btn gcd-download-primary" onClick={downloadChallanPdf}>
                  <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  Download Challan PDF
                </button>
                <button type="button" className="gcd-download-btn gcd-download-secondary" onClick={downloadListPdf}>
                  <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  Download Student List PDF
                </button>
              </div>
            </div>
          )}
        </div>

        {/* ── Footer ─────────────────────────────────────────────────── */}
        <div className="gcd-footer">
          {step === 1 && (
            <>
              <button type="button" className="gcd-btn gcd-btn-ghost" onClick={onClose}>Cancel</button>
              <button type="button" className="gcd-btn gcd-btn-gold" disabled={!canFetch} onClick={fetchStudents}>
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Fetch Students
              </button>
            </>
          )}

          {step === 2 && (
            <>
              <button type="button" className="gcd-btn gcd-btn-outline" onClick={() => setStep(1)}>← Back</button>
              <button type="button" className="gcd-btn gcd-btn-navy" disabled={selectedStudents.length === 0} onClick={() => setStep(3)}>
                Continue →
              </button>
            </>
          )}

          {step === 3 && (
            <>
              <button type="button" className="gcd-btn gcd-btn-outline" onClick={() => setStep(2)}>← Back</button>
              <button type="button" className="gcd-btn gcd-btn-navy" onClick={generateChallan}>
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                Generate Challan
              </button>
            </>
          )}

          {step === 'success' && (
            <button type="button" className="gcd-btn gcd-btn-navy" onClick={closeAndRefresh}>
              Close &amp; Refresh List
            </button>
          )}
        </div>
      </div>

      {/* All scoped CSS from GenerateChallanDialog.vue */}
      <style>{`
        .gcd-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px)}
        .gcd-modal{background:#fff;border-radius:20px;width:100%;max-width:580px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 32px 80px rgba(0,0,0,.22),0 0 0 1px rgba(255,255,255,.05);overflow:hidden}
        .gcd-header{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem 1rem;border-bottom:1px solid #F3F4F6;flex-shrink:0}
        .gcd-header-left{display:flex;flex-direction:column;gap:.5rem}
        .gcd-steps{display:flex;gap:5px}
        .gcd-step-dot{width:24px;height:4px;border-radius:9999px;background:#E5E7EB;transition:background .3s ease}
        .gcd-step-active{background:#1B3A6B}
        .gcd-step-done{background:#10B981}
        .gcd-title{font-size:1rem;font-weight:700;color:#111827}
        .gcd-close{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:none;background:#F3F4F6;border-radius:8px;cursor:pointer;color:#6B7280;transition:all .15s ease;flex-shrink:0}
        .gcd-close svg{width:16px;height:16px}
        .gcd-close:hover{background:#FEE2E2;color:#DC2626}
        .gcd-body{flex:1;overflow-y:auto;padding:1.5rem;min-height:0}
        .gcd-step-content{display:flex;flex-direction:column;gap:1rem}
        .gcd-step-desc{font-size:.875rem;color:#6B7280;line-height:1.5}
        .gcd-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .gcd-form-group{display:flex;flex-direction:column;gap:.375rem}
        .gcd-label{font-size:.8125rem;font-weight:600;color:#374151}
        .gcd-required{color:#EF4444}
        .gcd-select,.gcd-input{width:100%;padding:.5625rem .875rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.875rem;color:#111827;background:#fff;outline:none;transition:border-color .15s ease,box-shadow .15s ease;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .gcd-select:focus,.gcd-input:focus{border-color:#1B3A6B;box-shadow:0 0 0 3px rgba(27,58,107,.1)}
        .gcd-input-wrap{position:relative}
        .gcd-input-prefix{position:absolute;left:.875rem;top:50%;transform:translateY(-50%);font-size:.8125rem;font-weight:600;color:#9CA3AF;pointer-events:none}
        .gcd-input-prefixed{padding-left:2.5rem}
        .gcd-input-readonly{background:#F9FAFB;color:#374151;cursor:default}
        .gcd-field-hint{font-size:.75rem;color:#9CA3AF;margin:0}
        .gcd-error-banner{display:flex;align-items:center;gap:.5rem;background:#FEE2E2;color:#991B1B;border:1px solid #FECACA;border-radius:8px;padding:.625rem .875rem;font-size:.8125rem;font-weight:500}
        .gcd-summary-bar{display:flex;align-items:center;gap:1.25rem;background:#F8FAFC;border:1px solid #E5E7EB;border-radius:10px;padding:.75rem 1rem}
        .gcd-summary-stat{display:flex;align-items:baseline;gap:.375rem}
        .gcd-summary-num{font-size:1.25rem;font-weight:800;color:#111827}
        .gcd-summary-label{font-size:.75rem;color:#9CA3AF}
        .gcd-select-all-btn{margin-left:auto;font-size:.8125rem;font-weight:600;color:#1B3A6B;background:transparent;border:none;cursor:pointer;text-decoration:underline;text-underline-offset:2px}
        .gcd-student-list{display:flex;flex-direction:column;gap:2px;max-height:320px;overflow-y:auto;border:1px solid #E5E7EB;border-radius:10px;background:#fff}
        .gcd-student-row{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;cursor:pointer;transition:background .1s ease;border-bottom:1px solid #F3F4F6}
        .gcd-student-row:last-child{border-bottom:none}
        .gcd-student-row:hover{background:#F9FAFB}
        .gcd-student-selected{background:#EEF2FF !important}
        .gcd-student-name{font-size:.875rem;font-weight:600;color:#111827}
        .gcd-student-meta{font-size:.75rem;color:#9CA3AF;margin-top:1px}
        .gcd-toggle{width:36px;height:20px;border-radius:10px;background:#D1D5DB;position:relative;transition:background .2s ease;flex-shrink:0}
        .gcd-toggle-on{background:#1B3A6B}
        .gcd-toggle-thumb{position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s ease}
        .gcd-toggle-on .gcd-toggle-thumb{transform:translateX(16px)}
        .gcd-no-students{padding:2.5rem 1rem;text-align:center}
        .gcd-confirm-summary{background:#F8FAFC;border:1px solid #E5E7EB;border-radius:12px;overflow:hidden}
        .gcd-confirm-title{font-size:.875rem;font-weight:700;color:#374151;padding:.875rem 1rem;border-bottom:1px solid #E5E7EB;background:#fff}
        .gcd-confirm-rows{display:flex;flex-direction:column}
        .gcd-confirm-row{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #F3F4F6}
        .gcd-confirm-row:last-child{border-bottom:none}
        .gcd-confirm-total{background:#F0FDF4}
        .gcd-confirm-key{font-size:.8125rem;color:#6B7280}
        .gcd-confirm-val{font-size:.875rem;font-weight:600;color:#111827}
        .gcd-confirm-notice{display:flex;align-items:flex-start;gap:.625rem;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:.75rem 1rem}
        .gcd-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem 1.5rem;gap:1rem;min-height:200px}
        .gcd-spinner{width:40px;height:40px;border:3px solid #E5E7EB;border-top-color:#1B3A6B;border-radius:50%;animation:gcd-spin .7s linear infinite}
        @keyframes gcd-spin{to{transform:rotate(360deg)}}
        .gcd-loading-text{font-size:.875rem;color:#6B7280;font-weight:500}
        .gcd-success{display:flex;flex-direction:column;align-items:center;padding:2.5rem 1.5rem;gap:.75rem;text-align:center}
        .gcd-success-icon{width:64px;height:64px;background:linear-gradient(135deg,#10B981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:.25rem}
        .gcd-success-icon svg{width:30px;height:30px}
        .gcd-success-title{font-size:1.25rem;font-weight:800;color:#111827}
        .gcd-success-subtitle{font-size:.875rem;color:#6B7280}
        .gcd-invoice-no{font-family:'Courier New',monospace;color:#1B3A6B;letter-spacing:.05em}
        .gcd-download-buttons{display:flex;flex-direction:column;gap:.625rem;width:100%;margin-top:.5rem}
        .gcd-download-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.6875rem 1rem;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;transition:all .15s ease;border:none;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .gcd-download-primary{background:#1B3A6B;color:#fff}
        .gcd-download-primary:hover{background:#162d54}
        .gcd-download-secondary{background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB}
        .gcd-download-secondary:hover{background:#E5E7EB}
        .gcd-footer{padding:1rem 1.5rem;border-top:1px solid #F3F4F6;display:flex;justify-content:flex-end;gap:.75rem;flex-shrink:0;background:#FAFAFA}
        .gcd-btn{display:inline-flex;align-items:center;gap:.375rem;padding:.5625rem 1.125rem;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;transition:all .15s ease;border:none;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .gcd-btn svg{width:16px;height:16px;flex-shrink:0}
        .gcd-btn:disabled{opacity:.45;cursor:not-allowed}
        .gcd-btn-navy{background:#1B3A6B;color:#fff}.gcd-btn-navy:hover:not(:disabled){background:#162d54}
        .gcd-btn-gold{background:#C8960C;color:#fff}.gcd-btn-gold:hover:not(:disabled){background:#a87b0a}
        .gcd-btn-ghost{background:transparent;color:#6B7280;border:1.5px solid #E5E7EB}.gcd-btn-ghost:hover{background:#F3F4F6}
        .gcd-btn-outline{background:transparent;color:#374151;border:1.5px solid #D1D5DB}.gcd-btn-outline:hover{background:#F3F4F6}
        .capitalize{text-transform:capitalize}
        @media(max-width:480px){.gcd-form-grid{grid-template-columns:1fr}.gcd-modal{border-radius:16px}}
      `}</style>
    </div>
  );

  return createPortal(dialog, document.body);
}
