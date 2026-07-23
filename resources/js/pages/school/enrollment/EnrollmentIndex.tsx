import React, { useState, useMemo, useEffect, useRef } from 'react';
import { Link, router } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import { GenerateChallanDialog } from '@/components/school/GenerateChallanDialog';
import type { Student, Paginated, AcademicYear } from '@/types';


// ─── Types ────────────────────────────────────────────────────────────────────

interface Filters {
  search:      string;
  class_level: string;
  group:       string;
  status:      string;
}

interface EnrollmentIndexProps {
  students:    Paginated<Student>;
  filters:     Partial<Filters>;
  activeYear?: AcademicYear | null;
  stats:       { ssc_enrollment: number; hsc_enrollment: number };
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function statusLabel(status: string): string {
  const map: Record<string, string> = {
    draft:             'Draft',
    final:             'Needs Challan',
    pending_challan:   'Challan Pending Payment',
    challan_submitted: 'Challan Submitted',
    enrolled:          'Enrolled ✓',
  };
  return map[status] ?? status;
}

function statusClass(status: string): string {
  const map: Record<string, string> = {
    draft:             'ei-badge-gray',
    final:             'ei-badge-blue',
    pending_challan:   'ei-badge-amber',
    challan_submitted: 'ei-badge-blue',
    enrolled:          'ei-badge-green',
  };
  return map[status] ?? '';
}

// ─── Delete Confirm Dialog ────────────────────────────────────────────────────

interface DeleteConfirmProps {
  student: Student | null;
  isFinal: boolean;
  onCancel: () => void;
  onConfirm: () => void;
}

function DeleteConfirmDialog({ student, isFinal, onCancel, onConfirm }: DeleteConfirmProps) {
  if (!student) return null;
  return (
    <div className="ei-confirm-backdrop" onMouseDown={(e) => { if (e.target === e.currentTarget) onCancel(); }}>
      <div className="ei-confirm-box">
        <div className="ei-confirm-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <polyline points="3,6 5,6 21,6"/>
            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
            <path d="M10 11v6"/><path d="M14 11v6"/>
            <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
          </svg>
        </div>
        <h3 className="ei-confirm-title">{isFinal ? 'Delete Final Record?' : 'Delete Student?'}</h3>
        <p className="ei-confirm-desc">
          {isFinal
            ? <>Are you sure you want to delete <strong>{student.full_name}</strong>? This is a <strong>confirmed enrollment</strong> — deletion should only be for genuine mistakes before any invoice exists.</>
            : <>Are you sure you want to delete <strong>{student.full_name}</strong>? This action cannot be undone.</>
          }
        </p>
        <div className="ei-confirm-actions">
          <button type="button" className="ei-btn ei-btn-ghost ei-btn-sm" onClick={onCancel}>Cancel</button>
          <button type="button" className="ei-btn ei-btn-danger ei-btn-sm" onClick={onConfirm}>Delete</button>
        </div>
      </div>
    </div>
  );
}

// ─── Main Component ───────────────────────────────────────────────────────────

export default function EnrollmentIndex({
  students,
  filters: initialFilters,
  activeYear,
  stats,
}: EnrollmentIndexProps) {
  const [challanDialogOpen, setChallanDialogOpen] = useState(false);
  const [selectedIds,       setSelectedIds]       = useState<number[]>([]);
  const [deleteTarget,      setDeleteTarget]       = useState<Student | null>(null);

  const [filters, setFilters] = useState<Filters>({
    search:      initialFilters.search      ?? '',
    class_level: initialFilters.class_level ?? '',
    group:       initialFilters.group       ?? '',
    status:      initialFilters.status      ?? '',
  });

  const checkboxRef = useRef<HTMLInputElement>(null);

  const hasActiveFilters = useMemo(
    () => !!(filters.search || filters.class_level || filters.group || filters.status),
    [filters],
  );

  const selectableStudents = useMemo(
    () => students.data?.filter((s) => {
      const recStatus = s.academic_record?.status ?? s.status;
      return recStatus !== 'enrolled';
    }) ?? [],
    [students.data],
  );

  const allSelected  = selectableStudents.length > 0 && selectableStudents.every((s) => selectedIds.includes(s.id));
  const someSelected = selectedIds.length > 0 && !allSelected;

  useEffect(() => {
    if (checkboxRef.current) {
      checkboxRef.current.indeterminate = someSelected;
    }
  }, [someSelected]);

  function applyFilters() {
    const params: Record<string, string> = {};
    Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v; });
    router.get(route('school.students.index'), params, { preserveState: true, replace: true });
  }

  function clearFilters() {
    const cleared: Filters = { search: '', class_level: '', group: '', status: '' };
    setFilters(cleared);
    router.get(route('school.students.index'), {}, { preserveState: true, replace: true });
  }

  function toggleAll(e: React.ChangeEvent<HTMLInputElement>) {
    setSelectedIds(e.target.checked ? selectableStudents.map((s) => s.id) : []);
  }

  function toggleStudent(id: number, checked: boolean) {
    setSelectedIds((prev) => checked ? [...prev, id] : prev.filter((x) => x !== id));
  }

  function performDelete() {
    if (!deleteTarget) return;
    const isFinal = deleteTarget.academic_record?.status === 'final';
    router.delete(route('school.students.destroy', deleteTarget.id), {
      preserveScroll: true,
      data: isFinal ? { confirm_final_delete: true } : {},
      onSuccess: () => setDeleteTarget(null),
    });
  }

  function handleExportPdf() {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v) params.set(k, v); });
    window.open(route('school.documents.enrollment-forms.bulk-pdf') + '?' + params.toString(), '_blank');
  }

  function handleSinglePdf(student: Student) {
    window.open(route('school.documents.enrollment-form.pdf', student.id), '_blank');
  }

  const meta = students.meta;

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">Enrollment Forms</span>
        </nav>
      }
    >
      {/* ── Page Header ─────────────────────────────────────────────────── */}
      <div className="ei-page-header">
        <div>
          <h1 className="ei-page-title">Enrollment Forms</h1>
          <p className="ei-page-subtitle">
            {activeYear?.label ?? 'No active year'}
          </p>
        </div>

        <div className="ei-header-actions">
          <button type="button" className="ei-btn ei-btn-gold" onClick={() => setChallanDialogOpen(true)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
            Generate Challan
          </button>
          <Link href={route('school.students.create')} className="ei-btn ei-btn-navy">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Enrollment
          </Link>
        </div>
      </div>

      {/* ── Stat Cards (always show final-based counts) ────────────────────── */}
      <div className="ei-stats-row">
        <div className="ei-stat-card ei-stat-blue">
          <div className="ei-stat-label">SSC Enrollment</div>
          <div className="ei-stat-value">{stats.ssc_enrollment.toLocaleString()}</div>
        </div>
        <div className="ei-stat-card ei-stat-orange">
          <div className="ei-stat-label">HSC Enrollment</div>
          <div className="ei-stat-value">{stats.hsc_enrollment.toLocaleString()}</div>
        </div>
      </div>

      {/* ── Filters ─────────────────────────────────────────────────────── */}
      <div className="ei-filters-card">
        <div className="ei-filters-grid">
          <div className="ei-filter-group ei-filter-wide">
            <label className="ei-filter-label">Search</label>
            <div className="ei-search-wrap">
              <svg className="ei-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                type="text"
                className="ei-filter-input ei-filter-input-search"
                placeholder="Name, enrollment #, CNIC…"
                value={filters.search}
                onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                onKeyUp={(e) => e.key === 'Enter' && applyFilters()}
              />
            </div>
          </div>

          <div className="ei-filter-group">
            <label className="ei-filter-label">Class</label>
            <select
              className="ei-filter-select"
              value={filters.class_level}
              onChange={(e) => setFilters((f) => ({ ...f, class_level: e.target.value }))}
            >
              <option value="">All Classes</option>
              <option value="ssc_part1">SSC Part 1</option>
              <option value="ssc_part2">SSC Part 2</option>
              <option value="hsc_part1">HSC Part 1</option>
              <option value="hsc_part2">HSC Part 2</option>
            </select>
          </div>

          <div className="ei-filter-group">
            <label className="ei-filter-label">Group</label>
            <select
              className="ei-filter-select"
              value={filters.group}
              onChange={(e) => setFilters((f) => ({ ...f, group: e.target.value }))}
            >
              <option value="">All Groups</option>
              <option value="science">Science</option>
              <option value="arts">Arts</option>
              <option value="commerce">Commerce</option>
            </select>
          </div>

          <div className="ei-filter-group">
            <label className="ei-filter-label">Status</label>
            <select
              className="ei-filter-select"
              value={filters.status}
              onChange={(e) => setFilters((f) => ({ ...f, status: e.target.value }))}
            >
              <option value="">All Statuses</option>
              <option value="final">Needs Challan</option>
              <option value="pending_challan">Challan Pending Payment</option>
              <option value="enrolled">Enrolled</option>
            </select>
          </div>

          <div className="ei-filter-group ei-filter-action">
            <label className="ei-filter-label">&nbsp;</label>
            <div className="ei-filter-btns">
              <button type="button" className="ei-btn ei-btn-navy ei-btn-sm" onClick={applyFilters}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Filter
              </button>
              {hasActiveFilters && (
                <button type="button" className="ei-btn ei-btn-ghost ei-btn-sm" onClick={clearFilters}>Clear</button>
              )}
              <button type="button" className="ei-btn ei-btn-outline ei-btn-sm" onClick={handleExportPdf} title="Export current list as PDF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                  <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export PDF
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* ── Bulk Selection Bar ───────────────────────────────────────────── */}
      {selectedIds.length > 0 && (
        <div className="ei-bulk-bar">
          <div className="ei-bulk-left">
            <div className="ei-bulk-count">
              <strong>{selectedIds.length}</strong> student{selectedIds.length !== 1 ? 's' : ''} selected
            </div>
            <button type="button" className="ei-bulk-action" onClick={() => setChallanDialogOpen(true)}>
              <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
              </svg>
              Add to New Challan
            </button>
          </div>
          <button type="button" className="ei-bulk-clear" onClick={() => setSelectedIds([])}>
            Clear selection
          </button>
        </div>
      )}

      {/* ── Table Card ──────────────────────────────────────────────────── */}
      <div className="ei-table-card">
        <div className="ei-table-wrapper">
          <table className="ei-table">
            <thead>
              <tr>
                <th className="ei-th ei-th-check">
                  <input
                    ref={checkboxRef}
                    type="checkbox"
                    checked={allSelected}
                    onChange={toggleAll}
                    className="ei-checkbox"
                  />
                </th>
                <th className="ei-th">Srl</th>
                <th className="ei-th">Enrollment #</th>
                <th className="ei-th">Student Name</th>
                <th className="ei-th">Father Name</th>
                <th className="ei-th">Class</th>
                <th className="ei-th">Group</th>
                <th className="ei-th">CNIC / B-Form</th>
                <th className="ei-th">Status</th>
                <th className="ei-th ei-th-actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              {students.data?.map((student, index) => {
                const recStatus = student.academic_record?.status ?? 'draft';
                const isEnrolled = recStatus === 'enrolled' || student.status === 'enrolled';

                return (
                <tr
                  key={student.id}
                  className={`ei-row${selectedIds.includes(student.id) ? ' ei-row-selected' : ''}`}
                >
                  <td className="ei-td ei-td-check">
                    <input
                      type="checkbox"
                      value={student.id}
                      checked={selectedIds.includes(student.id)}
                      onChange={(e) => toggleStudent(student.id, e.target.checked)}
                      disabled={isEnrolled}
                      className="ei-checkbox"
                    />
                  </td>
                  <td className="ei-td ei-td-srl">
                    {((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? 20) + index + 1}
                  </td>
                  <td className="ei-td">
                    {student.enrollment_number ? (
                      <span className="ei-enrollment-no">{student.enrollment_number}</span>
                    ) : (
                      <span className="ei-pending-badge">
                        <svg className="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                          <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                        </svg>
                        Pending
                      </span>
                    )}
                  </td>
                  <td className="ei-td">
                    <div className="ei-student-name">{student.full_name}</div>
                    {student.urdu_name && (
                      <div className="ei-student-urdu">{student.urdu_name}</div>
                    )}
                  </td>
                  <td className="ei-td ei-td-secondary">{student.father_name}</td>
                  <td className="ei-td">
                    <span className={`ei-badge ${(student.academic_record?.class_level ?? '').includes('ssc') ? 'ei-badge-blue' : 'ei-badge-orange'}`}>
                      {(student.academic_record?.class_level ?? student.class_level ?? '').replace('_', ' ').toUpperCase()}
                    </span>
                  </td>
                  <td className="ei-td ei-td-secondary" style={{ textTransform: 'capitalize' }}>
                    {student.academic_record?.subject_group ?? student.subject_group ?? '—'}
                  </td>
                  <td className="ei-td">
                    <span className="ei-mono">{student.cnic_or_bform ?? student.cnic ?? student.b_form ?? '—'}</span>
                  </td>
                  <td className="ei-td">
                    <span className={`ei-badge ${statusClass(recStatus)}`}>
                      {statusLabel(recStatus)}
                    </span>
                  </td>
                  <td className="ei-td ei-td-actions">
                    <div className="ei-actions">
                      {/* View */}
                      <Link href={route('school.students.show', student.id)} className="ei-action-btn ei-action-view" title="View">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                      </Link>

                      {/* PDF */}
                      <button type="button" className="ei-action-btn ei-action-pdf" title="Export PDF" onClick={() => handleSinglePdf(student)}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                          <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                      </button>

                      {/* Edit — hide for enrolled */}
                      {!isEnrolled && (
                        <Link href={route('school.students.edit', student.id)} className="ei-action-btn ei-action-edit" title="Edit">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </Link>
                      )}

                      {/* Delete */}
                      {!isEnrolled && (
                        <button type="button" className="ei-action-btn ei-action-delete" title="Delete" onClick={() => setDeleteTarget(student)}>
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <polyline points="3,6 5,6 21,6"/>
                            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6"/><path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                          </svg>
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              )})}

              {!students.data?.length && (
                <tr>
                  <td colSpan={10} className="ei-empty-cell">
                    <div className="ei-empty">
                      <div className="ei-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round">
                          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                          <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                      </div>
                      <h3 className="ei-empty-title">
                        No enrollments found
                      </h3>
                      <p className="ei-empty-desc">
                        {hasActiveFilters
                          ? 'No students match the selected filters.'
                          : 'No enrollments have been confirmed yet.'
                        }
                      </p>
                      <div className="ei-empty-actions">
                        {hasActiveFilters && (
                          <button type="button" className="ei-btn ei-btn-ghost ei-btn-sm" onClick={clearFilters}>
                            Clear Filters
                          </button>
                        )}
                        {!hasActiveFilters && (
                          <Link href={route('school.students.create')} className="ei-btn ei-btn-navy ei-btn-sm">
                            Enrollment
                          </Link>
                        )}
                      </div>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {(meta?.last_page ?? 0) > 1 && (
          <div className="ei-pagination">
            <span className="ei-pagination-info">
              Showing {meta?.from}–{meta?.to} of {meta?.total} students
            </span>
            <div className="ei-pagination-links">
              {meta?.links.map((link) => (
                <Link
                  key={link.label}
                  href={link.url ?? '#'}
                  preserveScroll
                  className={[
                    'ei-page-btn',
                    link.active  ? 'ei-page-active'   : '',
                    !link.url    ? 'ei-page-disabled'  : '',
                  ].join(' ')}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          </div>
        )}
      </div>

      {/* ── Dialogs ──────────────────────────────────────────────────────── */}
      <DeleteConfirmDialog
        student={deleteTarget}
        isFinal={deleteTarget?.academic_record?.status === 'final'}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={performDelete}
      />

      {/* Generate Challan Dialog */}

      <GenerateChallanDialog
        show={challanDialogOpen}
        selectedIds={selectedIds}
        onClose={() => setChallanDialogOpen(false)}
        onSuccess={() => { setChallanDialogOpen(false); setSelectedIds([]); }}
      />

      {/* All scoped CSS */}
      <style>{`
        .ei-page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.1rem;flex-wrap:wrap}
        .ei-page-title{font-size:1.55rem;font-weight:800;color:#0F172A;line-height:1.2}
        .ei-page-subtitle{font-size:.92rem;color:#64748B;margin-top:.35rem}
        .ei-header-actions{display:flex;align-items:center;gap:.75rem;flex-shrink:0;flex-wrap:wrap}
        .ei-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1.15rem;border-radius:16px;font-size:.875rem;font-weight:700;cursor:pointer;transition:all .18s ease;border:1px solid transparent;text-decoration:none;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;white-space:nowrap;box-shadow:0 12px 26px rgba(148,163,184,.12)}
        .ei-btn svg{width:16px;height:16px;flex-shrink:0}
        .ei-btn-navy{background:linear-gradient(135deg,#4F7CFF 0%,#6D9DFF 100%);color:#fff}.ei-btn-navy:hover{transform:translateY(-1px);box-shadow:0 18px 30px rgba(79,124,255,.28)}
        .ei-btn-gold{background:linear-gradient(135deg,#14B8A6 0%,#0EA5E9 100%);color:#fff}.ei-btn-gold:hover{transform:translateY(-1px);box-shadow:0 18px 30px rgba(14,165,233,.24)}
        .ei-btn-ghost{background:#FFFFFF;color:#64748B;border-color:#E2E8F0}.ei-btn-ghost:hover{background:#F8FBFF;color:#334155;border-color:#CBD5E1}
        .ei-btn-outline{background:rgba(255,255,255,.86);color:#334155;border-color:#D7E3F4}.ei-btn-outline:hover{background:#FFFFFF;border-color:#BFDBFE;color:#0F172A}
        .ei-btn-danger{background:linear-gradient(135deg,#FB7185 0%,#EF4444 100%);color:#fff}.ei-btn-danger:hover{transform:translateY(-1px)}
        .ei-btn-sm{padding:.58rem .95rem;font-size:.8125rem;border-radius:14px}

        .ei-tabs{display:inline-flex;gap:.45rem;margin-bottom:1rem;padding:.45rem;background:rgba(255,255,255,.72);border:1px solid #E2E8F0;border-radius:20px;box-shadow:0 14px 30px rgba(148,163,184,.1)}
        .ei-tab{display:flex;align-items:center;gap:.5rem;padding:.72rem 1.15rem;font-size:.875rem;font-weight:700;color:#64748B;background:transparent;border:none;cursor:pointer;border-radius:16px;transition:all .18s ease;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .ei-tab:hover{color:#334155;background:#F8FBFF}
        .ei-tab-active{color:#1D4ED8;background:linear-gradient(135deg,#EEF4FF 0%,#F8FBFF 100%);box-shadow:0 10px 20px rgba(79,124,255,.16)}
        .ei-tab-badge{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:20px;padding:0 .375rem;border-radius:9999px;font-size:.6875rem;font-weight:800}
        .ei-tab-badge-draft{background:#E2E8F0;color:#475569}
        .ei-tab-badge-final{background:#DBEAFE;color:#1E40AF}
        .ei-tab-active .ei-tab-badge-draft,.ei-tab-active .ei-tab-badge-final{background:#1D4ED8;color:#fff}

        .ei-stats-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
        .ei-stat-card{border-radius:24px;padding:1.2rem 1.35rem;color:#fff;position:relative;overflow:hidden;box-shadow:0 20px 40px rgba(148,163,184,.16)}
        .ei-stat-card::after{content:'';position:absolute;right:-18px;bottom:-24px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.1)}
        .ei-stat-blue{background:linear-gradient(135deg,#4F7CFF 0%,#6D9DFF 100%)}
        .ei-stat-orange{background:linear-gradient(135deg,#9B8CFF 0%,#7C6CFF 100%)}
        .ei-stat-label{font-size:.76rem;font-weight:700;opacity:.92;text-transform:uppercase;letter-spacing:.08em}
        .ei-stat-value{font-size:2rem;font-weight:800;margin-top:.35rem;line-height:1}

        .ei-filters-card{background:linear-gradient(180deg,#FFFFFF 0%,#F8FBFF 100%);border:1px solid #E2E8F0;border-radius:24px;padding:1.15rem 1.25rem;margin-bottom:1rem;box-shadow:0 20px 45px rgba(148,163,184,.12)}
        .ei-filters-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.85rem;align-items:end}
        .ei-filter-group{display:flex;flex-direction:column;gap:.38rem}
        .ei-filter-label{font-size:.72rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.08em}
        .ei-search-wrap{position:relative}
        .ei-search-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:#94A3B8;pointer-events:none}
        .ei-filter-input,.ei-filter-select{width:100%;padding:.8rem .95rem;border:1px solid #D7E3F4;border-radius:16px;font-size:.875rem;color:#0F172A;background:#FFFFFF;outline:none;transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;box-shadow:0 6px 18px rgba(148,163,184,.08)}
        .ei-filter-input:focus,.ei-filter-select:focus{border-color:#60A5FA;box-shadow:0 0 0 4px rgba(96,165,250,.12)}
        .ei-filter-input-search{padding-left:2.55rem}
        .ei-filter-btns{display:flex;gap:.55rem;align-items:center;flex-wrap:wrap}
        .ei-bulk-bar{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#EEF4FF 0%,#F8FBFF 100%);border:1px solid #BFDBFE;border-radius:20px;padding:.9rem 1rem;margin-bottom:1rem;font-size:.875rem;box-shadow:0 16px 28px rgba(96,165,250,.12)}
        .ei-bulk-left{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
        .ei-bulk-count{font-weight:700;color:#1D4ED8}
        .ei-bulk-action{display:flex;align-items:center;gap:.4rem;background:#1D4ED8;color:#fff;padding:.55rem .9rem;border-radius:14px;font-size:.8125rem;font-weight:700;cursor:pointer;border:none;transition:all .18s ease;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;box-shadow:0 10px 20px rgba(29,78,216,.22)}
        .ei-bulk-action:hover{transform:translateY(-1px);background:#1E40AF}
        .ei-bulk-clear{font-size:.8125rem;color:#64748B;background:transparent;border:none;cursor:pointer;text-decoration:underline;text-underline-offset:2px}
        .ei-table-card{background:linear-gradient(180deg,#FFFFFF 0%,#FBFDFF 100%);border:1px solid #E2E8F0;border-radius:28px;overflow:hidden;box-shadow:0 24px 55px rgba(148,163,184,.12)}
        .ei-table-wrapper{overflow-x:auto}
        .ei-table{width:100%;border-collapse:separate;border-spacing:0;font-size:.875rem}
        .ei-th{padding:.95rem 1rem;text-align:left;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;background:#F8FBFF;border-bottom:1px solid #E2E8F0;white-space:nowrap}
        .ei-th-check{width:44px}.ei-th-actions{text-align:right}
        .ei-row{transition:background .14s ease}
        .ei-row:hover td{background:#F8FBFF}
        .ei-row-selected td{background:#EEF4FF !important}
        .ei-td{padding:.95rem 1rem;color:#334155;border-bottom:1px solid #EEF2F7;vertical-align:middle;background:transparent}
        .ei-td-check{width:44px}.ei-td-srl{color:#94A3B8;font-size:.8125rem;width:48px}
        .ei-td-secondary{color:#64748B;font-size:.8125rem}.ei-td-actions{width:170px}
        .ei-table tbody tr:last-child td{border-bottom:none}
        .ei-checkbox{width:16px;height:16px;border-radius:4px;accent-color:#4F7CFF;cursor:pointer}
        .ei-enrollment-no{font-family:'Courier New',monospace;font-weight:700;font-size:.8125rem;color:#1D4ED8;letter-spacing:.05em}
        .ei-pending-badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.75rem;font-weight:700;color:#B45309;background:#FFF7ED;border:1px solid #FED7AA;padding:.28rem .6rem;border-radius:9999px}
        .ei-student-name{font-weight:700;color:#0F172A}
        .ei-student-urdu{font-size:.75rem;color:#94A3B8;direction:rtl;font-family:'Noto Nastaliq Urdu',serif;margin-top:1px}
        .ei-mono{font-family:'Courier New',monospace;font-size:.8125rem;color:#334155}
        .ei-badge{display:inline-flex;align-items:center;padding:.28rem .66rem;border-radius:9999px;font-size:.6875rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
        .ei-badge-blue{background:#DBEAFE;color:#1E40AF}
        .ei-badge-orange{background:#F3E8FF;color:#7C3AED}
        .ei-badge-amber{background:#FEF3C7;color:#92400E}
        .ei-badge-green{background:#DCFCE7;color:#166534}
        .ei-badge-gray{background:#E2E8F0;color:#475569}
        .ei-actions{display:flex;align-items:center;justify-content:flex-end;gap:.35rem}
        .ei-action-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:12px;border:none;cursor:pointer;transition:all .18s ease;text-decoration:none;box-shadow:0 8px 18px rgba(148,163,184,.12)}
        .ei-action-btn svg{width:15px;height:15px}
        .ei-action-btn:hover{transform:translateY(-1px)}
        .ei-action-view{background:#EEF2FF;color:#4F46E5}.ei-action-view:hover{background:#E0E7FF}
        .ei-action-edit{background:#ECFDF5;color:#059669}.ei-action-edit:hover{background:#D1FAE5}
        .ei-action-delete{background:#FEF2F2;color:#DC2626}.ei-action-delete:hover{background:#FEE2E2}
        .ei-action-finalize{background:#DBEAFE;color:#1D4ED8}.ei-action-finalize:hover{background:#BFDBFE}
        .ei-action-pdf{background:#F5F3FF;color:#7C3AED}.ei-action-pdf:hover{background:#EDE9FE}
        .ei-empty-cell{padding:0 !important}
        .ei-empty{display:flex;flex-direction:column;align-items:center;padding:4rem 2rem;text-align:center}
        .ei-empty-icon{width:72px;height:72px;background:linear-gradient(180deg,#F8FBFF 0%,#EEF4FF 100%);border:1px solid #D7E3F4;border-radius:22px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;color:#A5B4FC;box-shadow:0 14px 30px rgba(148,163,184,.12)}
        .ei-empty-icon svg{width:30px;height:30px}
        .ei-empty-title{font-size:1.05rem;font-weight:800;color:#334155;margin-bottom:.45rem}
        .ei-empty-desc{font-size:.875rem;color:#94A3B8;margin-bottom:1.35rem;max-width:32rem}
        .ei-empty-actions{display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center}
        .ei-pagination{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-top:1px solid #EEF2F7;font-size:.875rem;background:#FCFEFF}
        .ei-pagination-info{color:#64748B}
        .ei-pagination-links{display:flex;gap:.35rem;flex-wrap:wrap}
        .ei-page-btn{min-width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:.8125rem;font-weight:700;text-decoration:none;color:#334155;transition:all .18s ease;padding:0 .65rem;background:#FFFFFF;border:1px solid transparent}
        .ei-page-btn:hover:not(.ei-page-disabled):not(.ei-page-active){background:#F8FBFF;border-color:#D7E3F4}
        .ei-page-active{background:#1D4ED8;color:#fff !important;font-weight:800;box-shadow:0 10px 20px rgba(29,78,216,.2)}
        .ei-page-disabled{opacity:.35;pointer-events:none}
        .ei-confirm-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.28);z-index:110;display:flex;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(6px)}
        .ei-confirm-box{background:linear-gradient(180deg,#FFFFFF 0%,#F8FBFF 100%);border:1px solid #E2E8F0;border-radius:24px;padding:1.85rem 1.5rem;width:100%;max-width:400px;text-align:center;box-shadow:0 24px 64px rgba(15,23,42,.16)}
        .ei-confirm-icon{width:56px;height:56px;background:#FEF2F2;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#DC2626}
        .ei-confirm-icon svg{width:24px;height:24px}
        .ei-confirm-title{font-size:1.08rem;font-weight:800;color:#0F172A;margin-bottom:.5rem}
        .ei-confirm-desc{font-size:.875rem;color:#64748B;line-height:1.6;margin-bottom:1.3rem}
        .ei-confirm-actions{display:flex;justify-content:center;gap:.75rem;flex-wrap:wrap}
        @media(max-width:960px){.ei-filters-grid{grid-template-columns:1fr 1fr}.ei-filter-wide{grid-column:span 2}.ei-stats-row{grid-template-columns:1fr}}
        @media(max-width:640px){.ei-page-header{flex-direction:column;align-items:stretch}.ei-header-actions{justify-content:flex-start}.ei-tabs{display:flex;width:100%}.ei-filters-grid{grid-template-columns:1fr}.ei-filter-wide{grid-column:span 1}.ei-pagination{flex-direction:column;gap:.75rem;align-items:flex-start}.ei-stats-row{grid-template-columns:1fr}.ei-bulk-bar{flex-direction:column;align-items:flex-start;gap:.75rem}}
      `}</style>
    </SchoolLayout>
  );
}
