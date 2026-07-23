import React, { useState, useMemo } from 'react';
import { Link, router } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import { GenerateChallanDialog } from '@/components/school/GenerateChallanDialog';
import { SaveFinalDialog } from '@/components/school/SaveFinalDialog';
import type { ExamForm, Paginated, AcademicYear, School } from '@/types';

// ─── Types ────────────────────────────────────────────────────────────────────
interface Filters {
  search: string;
  class_level: string;
  group: string;
  status: string;
}

interface ExamFormIndexProps {
  examForms: Paginated<ExamForm>;
  filters: Partial<Filters & { tab: string }>;
  activeYear?: AcademicYear | null;
  tab: 'draft' | 'final';
  draftCount: number;
  finalCount: number;
  stats: {
    ssc_count: number;
    hsc_count: number;
    fees_paid: number;
    fees_unpaid: number;
    gap_count: number;
  };
  school?: School | null;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function statusLabel(status: string): string {
  const map: Record<string, string> = {
    draft: 'Draft',
    final: 'Final',
    pending_challan: 'Awaiting Challan',
    challan_submitted: 'Challan Submitted',
    paid: 'Paid',
    verified: 'Verified',
  };
  return map[status] ?? status;
}

function statusClass(status: string): string {
  const map: Record<string, string> = {
    draft: 'ex-badge-gray',
    final: 'ex-badge-blue',
    pending_challan: 'ex-badge-amber',
    challan_submitted: 'ex-badge-blue',
    paid: 'ex-badge-green',
    verified: 'ex-badge-green',
  };
  return map[status] ?? '';
}

// ─── Gradient Stat Cards Sub-Component ────────────────────────────────────────
interface GradientCardProps {
  title: string;
  value: number | string;
  gradient: string;
  icon: React.ReactNode;
  onClick?: () => void;
  isPulsing?: boolean;
}

function GradientStatCard({ title, value, gradient, icon, onClick, isPulsing }: GradientCardProps) {
  return (
    <div
      className={`ex-stat-card ${onClick ? 'ex-stat-hover' : ''} ${isPulsing && Number(value) > 0 ? 'ex-stat-pulse' : ''}`}
      onClick={onClick}
      style={{ background: gradient }}
    >
      <div className="ex-stat-icon">{icon}</div>
      <div className="ex-stat-value">{value}</div>
      <div className="ex-stat-label">{title}</div>
    </div>
  );
}

// ─── Icons ────────────────────────────────────────────────────────────────────
const SscIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
    <path d="M12 14l-9 5 9 5 9-5"/>
  </svg>
);

const HscIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
    <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
  </svg>
);

const FeesPaidIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M9 11l3 3L22 4"/>
    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
  </svg>
);

const FeesUnpaidIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <circle cx="12" cy="12" r="10"/>
    <line x1="12" y1="8" x2="12" y2="12"/>
    <line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg>
);

const GapIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 9v4"/>
    <path d="M12 17h.01"/>
    <path d="M7.003 5H4.003v16h9.002"/>
    <path d="M16 5H20"/>
    <path d="M20 5l-4-4"/>
    <path d="M20 5l-4 4"/>
  </svg>
);

const PlusIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
    <line x1="12" y1="5" x2="12" y2="19"/>
    <line x1="5" y1="12" x2="19" y2="12"/>
  </svg>
);

const ReceiptIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="2" y="5" width="20" height="14" rx="2"/>
    <line x1="2" y1="10" x2="22" y2="10"/>
  </svg>
);

const EyeIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
    <circle cx="12" cy="12" r="3"/>
  </svg>
);

const EditIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
  </svg>
);

const TrashIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <polyline points="3,6 5,6 21,6"/>
    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
    <path d="M10 11v6"/>
    <path d="M14 11v6"/>
    <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
  </svg>
);

const SearchIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <circle cx="11" cy="11" r="8"/>
    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
  </svg>
);

const DownloadIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
    <polyline points="7,10 12,15 17,10"/>
    <line x1="12" y1="15" x2="12" y2="3"/>
  </svg>
);

const CopyIcon = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
  </svg>
);

// ─── Main Page Component ───────────────────────────────────────────────────────
export default function ExamFormIndex({
  examForms,
  filters: initialFilters,
  activeYear,
  tab: initialTab,
  draftCount,
  finalCount,
  stats,
  school,
}: ExamFormIndexProps) {
  const [activeTab, setActiveTab] = useState<'draft' | 'final'>(initialTab ?? 'draft');
  const [challanOpen, setChallanOpen] = useState(false);
  const [saveFinalTarget, setSaveFinalTarget] = useState<ExamForm | null>(null);

  const [filters, setFilters] = useState<Filters>({
    search: initialFilters.search ?? '',
    class_level: initialFilters.class_level ?? '',
    group: initialFilters.group ?? '',
    status: initialFilters.status ?? '',
  });

  const [cardFilter, setCardFilter] = useState<'all' | 'ssc' | 'hsc' | 'paid' | 'unpaid' | 'gap'>('all');
  const hasActiveFilters = useMemo(() =>
    !!(filters.search || filters.class_level || filters.group || filters.status || cardFilter !== 'all'),
    [filters, cardFilter]
  );

  const meta = examForms.meta;
  const showHsc = school?.allowed_levels?.includes('intermediate') ?? true;

  // ─── Event Handlers ─────────────────────────────────────────────────────────
  function switchTab(newTab: 'draft' | 'final') {
    setActiveTab(newTab);
    const params: Record<string, string> = { tab: newTab };
    Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v; });
    router.get(route('school.examination.forms'), params, { preserveState: false });
  }

  function applyFilters() {
    const params: Record<string, string> = { tab: activeTab };
    Object.entries(filters).forEach(([k, v]) => { if (v) params[k] = v; });
    if (cardFilter !== 'all') {
      if (cardFilter === 'ssc') params.class_level = 'ssc';
      if (cardFilter === 'hsc') params.class_level = 'hsc';
      if (cardFilter === 'paid') params.status = 'paid';
      if (cardFilter === 'unpaid') params.status = 'unpaid';
    }
    router.get(route('school.examination.forms'), params, { preserveState: true, replace: true });
  }

  function clearFilters() {
    const cleared: Filters = { search: '', class_level: '', group: '', status: '' };
    setFilters(cleared);
    setCardFilter('all');
    router.get(route('school.examination.forms'), { tab: activeTab }, { preserveState: true, replace: true });
  }

  function handleCardClick(filter: 'ssc' | 'hsc' | 'paid' | 'unpaid' | 'gap') {
    if (filter === 'gap') {
      router.visit(route('school.examination.gap-report'));
    } else {
      setCardFilter(filter);
      const params: Record<string, string> = { tab: activeTab };
      if (filter === 'ssc') params.class_level = 'ssc';
      if (filter === 'hsc') params.class_level = 'hsc';
      if (filter === 'paid') params.status = 'paid';
      if (filter === 'unpaid') params.status = 'unpaid';
      router.get(route('school.examination.forms'), params, { preserveState: true, replace: true });
    }
  }

  function handleExportPdf() {
    const params = new URLSearchParams();
    params.set('tab', activeTab);
    Object.entries(filters).forEach(([k, v]) => { if (v) params.set(k, v); });
    window.open(route('school.documents.exam-forms.bulk-pdf') + '?' + params.toString(), '_blank');
  }

  function handleCopyToClipboard(text: string) {
    navigator.clipboard.writeText(text);
  }

  // ─── Render ─────────────────────────────────────────────────────────────────
  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">Examination Forms</span>
          <span className="text-gray-300 mx-2">|</span>
          <Link
            href={route('school.examination.gap-report')}
            className="text-amber-600 hover:text-amber-700 font-medium text-xs"
          >
            View Missing Students
          </Link>
        </nav>
      }
    >
      {/* ── Page Header & Buttons ──────────────────────────────────────────── */}
      <div className="ex-page-header">
        <div>
          <h1 className="ex-page-title">Examination Forms</h1>
          <p className="ex-page-subtitle">{activeYear?.label ?? 'No active year'}</p>
        </div>

        <div className="ex-header-actions">
          <Link
            href={route('school.examination.form.create', { mode: 'new' })}
            className="ex-btn ex-btn-primary"
          >
            {PlusIcon}
            Add SSC-I / HSC-I
          </Link>

          <div className="ex-btn-divider" />

          <Link
            href={route('school.examination.form.create', { mode: 'returning' })}
            className="ex-btn ex-btn-secondary"
          >
            {PlusIcon}
            Add SSC-II / HSC-II
          </Link>

          <div className="ex-btn-divider" />

          <button
            type="button"
            className="ex-btn ex-btn-gold"
            onClick={() => setChallanOpen(true)}
          >
            {ReceiptIcon}
            Generate Challan
          </button>
        </div>
      </div>

      {/* ── Gradient Stats Cards ───────────────────────────────────────────── */}
      <div className="ex-stats-row">
        <GradientStatCard
          title="SSC Exam Forms"
          value={stats.ssc_count}
          gradient="linear-gradient(135deg, #3B82F6 0%, #6366F1 100%)"
          icon={SscIcon}
          onClick={() => handleCardClick('ssc')}
        />

        {showHsc && (
          <GradientStatCard
            title="HSC Exam Forms"
            value={stats.hsc_count}
            gradient="linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%)"
            icon={HscIcon}
            onClick={() => handleCardClick('hsc')}
          />
        )}

        <GradientStatCard
          title="Fees Paid"
          value={stats.fees_paid}
          gradient="linear-gradient(135deg, #10B981 0%, #34D399 100%)"
          icon={FeesPaidIcon}
          onClick={() => handleCardClick('paid')}
        />

        <GradientStatCard
          title="Fees Unpaid"
          value={stats.fees_unpaid}
          gradient="linear-gradient(135deg, #F59E0B 0%, #EF4444 100%)"
          icon={FeesUnpaidIcon}
          onClick={() => handleCardClick('unpaid')}
          isPulsing
        />

        <GradientStatCard
          title="Enrollment to Exam Gap"
          value={stats.gap_count}
          gradient="linear-gradient(135deg, #F97316 0%, #EF4444 100%)"
          icon={GapIcon}
          onClick={() => handleCardClick('gap')}
          isPulsing
        />
      </div>

      {/* ── Tabs ──────────────────────────────────────────────────────────── */}
      <div className="ex-tabs">
        <button
          type="button"
          className={`ex-tab ${activeTab === 'draft' ? 'ex-tab-active' : ''}`}
          onClick={() => switchTab('draft')}
        >
          Draft
          <span className={`ex-tab-badge ${activeTab === 'draft' ? 'ex-tab-badge-active' : 'ex-tab-badge-draft'}`}>
            {draftCount}
          </span>
        </button>

        <button
          type="button"
          className={`ex-tab ${activeTab === 'final' ? 'ex-tab-active' : ''}`}
          onClick={() => switchTab('final')}
        >
          Final
          <span className={`ex-tab-badge ${activeTab === 'final' ? 'ex-tab-badge-active' : 'ex-tab-badge-final'}`}>
            {finalCount}
          </span>
        </button>
      </div>

      {/* ── Filters ───────────────────────────────────────────────────────── */}
      <div className="ex-filters-card">
        <div className="ex-filters-grid">
          <div className="ex-filter-group ex-filter-wide">
            <label className="ex-filter-label">Search</label>
            <div className="ex-search-wrap">
              <span className="ex-search-icon">{SearchIcon}</span>
              <input
                type="text"
                className="ex-filter-input ex-filter-input-search"
                placeholder="Student name, enrollment number…"
                value={filters.search}
                onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                onKeyUp={(e) => e.key === 'Enter' && applyFilters()}
              />
            </div>
          </div>

          <div className="ex-filter-group">
            <label className="ex-filter-label">Class</label>
            <select
              className="ex-filter-select"
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

          <div className="ex-filter-group">
            <label className="ex-filter-label">Group</label>
            <select
              className="ex-filter-select"
              value={filters.group}
              onChange={(e) => setFilters((f) => ({ ...f, group: e.target.value }))}
            >
              <option value="">All Groups</option>
              <option value="science">Science</option>
              <option value="arts">Arts</option>
              <option value="commerce">Commerce</option>
              <option value="general">General</option>
            </select>
          </div>

          <div className="ex-filter-group">
            <label className="ex-filter-label">Status</label>
            <select
              className="ex-filter-select"
              value={filters.status}
              onChange={(e) => setFilters((f) => ({ ...f, status: e.target.value }))}
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="final">Final</option>
              <option value="paid">Paid</option>
              <option value="verified">Verified</option>
            </select>
          </div>

          <div className="ex-filter-group ex-filter-action">
            <label className="ex-filter-label">&nbsp;</label>
            <div className="ex-filter-btns">
              <button type="button" className="ex-btn ex-btn-primary ex-btn-sm" onClick={applyFilters}>
                Filter
              </button>
              {hasActiveFilters && (
                <button type="button" className="ex-btn ex-btn-ghost ex-btn-sm" onClick={clearFilters}>
                  Clear Filters
                </button>
              )}
              <button type="button" className="ex-btn ex-btn-outline ex-btn-sm" onClick={handleExportPdf}>
                {DownloadIcon}
                Export PDF
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* ── Table ─────────────────────────────────────────────────────────── */}
      <div className="ex-table-card">
        <div className="ex-table-wrapper">
          <table className="ex-table">
            <thead>
              <tr>
                <th className="ex-th">Srl</th>
                <th className="ex-th">Student</th>
                <th className="ex-th">Class / Group</th>
                <th className="ex-th">Enrollment No</th>
                <th className="ex-th">Seat No</th>
                <th className="ex-th">Fee Status</th>
                <th className="ex-th ex-th-actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              {examForms.data?.map((form, index) => {
                const isDraft = form.status === 'draft';
                const serial = ((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? 20) + index + 1;
                return (
                  <tr key={form.id} className="ex-row">
                    <td className="ex-td ex-td-srl">{serial}</td>
                    <td className="ex-td">
                      <div className="ex-student-name">{form.student?.full_name}</div>
                      {form.student?.father_name && (
                        <div className="ex-student-father">{form.student.father_name}</div>
                      )}
                    </td>
                    <td className="ex-td">
                      <span className="ex-badge ex-badge-blue">
                        {form.student_academic_record?.class_level?.replace('_', ' ').toUpperCase()}
                      </span>
                      <span className="ex-student-group">
                        {form.student_academic_record?.subject_group}
                      </span>
                    </td>
                    <td className="ex-td">
                      {form.student?.enrollment_number ? (
                        <button
                          type="button"
                          className="ex-copy-btn"
                          onClick={() => handleCopyToClipboard(form.student!.enrollment_number!)}
                          title="Click to copy"
                        >
                          <span className="ex-enrollment-no">{form.student.enrollment_number}</span>
                          {CopyIcon}
                        </button>
                      ) : (
                        <span className="ex-pending-badge">Pending</span>
                      )}
                    </td>
                    <td className="ex-td">
                      {form.seat_number ? (
                        <span className="ex-seat-no">{form.seat_number}</span>
                      ) : (
                        <span className="ex-pending-badge">Pending</span>
                      )}
                    </td>
                    <td className="ex-td">
                      <span className={`ex-badge ${statusClass(form.status)}`}>
                        {statusLabel(form.status)}
                      </span>
                    </td>
                    <td className="ex-td ex-td-actions">
                      <div className="ex-actions">
                        <Link
                          href={route('school.examination.form.show', form.id)}
                          className="ex-action-btn ex-action-view"
                          title="View"
                        >
                          {EyeIcon}
                        </Link>

                        {isDraft && (
                          <Link
                            href={route('school.examination.form.edit', form.id)}
                            className="ex-action-btn ex-action-edit"
                            title="Edit"
                          >
                            {EditIcon}
                          </Link>
                        )}

                        {isDraft && (
                          <button
                            type="button"
                            className="ex-action-btn ex-action-finalize"
                            title="Save Final"
                            onClick={() => setSaveFinalTarget(form)}
                          >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                              <path d="M9 11l3 3L22 4"/>
                              <circle cx="12" cy="12" r="10" strokeWidth="2"/>
                            </svg>
                          </button>
                        )}

                        {isDraft && (
                          <button
                            type="button"
                            className="ex-action-btn ex-action-delete"
                            title="Delete"
                            onClick={() => {
                              if (confirm('Delete this draft exam form?')) {
                                router.delete(route('school.examination.form.destroy', form.id));
                              }
                            }}
                          >
                            {TrashIcon}
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                );
              })}

              {!examForms.data?.length && (
                <tr>
                  <td colSpan={7} className="ex-empty-cell">
                    <div className="ex-empty">
                      <div className="ex-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                          <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                          <circle cx="9" cy="7" r="4"/>
                          <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                          <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                      </div>
                      <h3 className="ex-empty-title">No examination forms found</h3>
                      <p className="ex-empty-desc">
                        {hasActiveFilters ? 'No records match the selected filters.' : 'Start by creating an examination form.'}
                      </p>
                      <div className="ex-empty-actions">
                        {hasActiveFilters ? (
                          <button type="button" className="ex-btn ex-btn-ghost ex-btn-sm" onClick={clearFilters}>
                            Clear Filters
                          </button>
                        ) : (
                          <Link href={route('school.examination.form.create', { mode: 'new' })} className="ex-btn ex-btn-primary ex-btn-sm">
                            Add First Exam Form
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
          <div className="ex-pagination">
            <span className="ex-pagination-info">
              Showing {meta?.from}–{meta?.to} of {meta?.total} examination forms
            </span>
            <div className="ex-pagination-links">
              {meta?.links.map((link) => (
                <Link
                  key={link.label}
                  href={link.url ?? '#'}
                  preserveScroll
                  className={[
                    'ex-page-btn',
                    link.active ? 'ex-page-active' : '',
                    !link.url ? 'ex-page-disabled' : '',
                  ].join(' ')}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          </div>
        )}
      </div>

      {/* ── Dialogs ───────────────────────────────────────────────────────── */}
      <GenerateChallanDialog
        show={challanOpen}
        onClose={() => setChallanOpen(false)}
        onSuccess={() => { setChallanOpen(false); }}
        invoiceType="examination"
      />

      <SaveFinalDialog
        student={saveFinalTarget ? (saveFinalTarget.student as any) : null}
        recordId={saveFinalTarget?.id}
        onCancel={() => setSaveFinalTarget(null)}
        onSuccess={() => { setSaveFinalTarget(null); router.reload(); }}
        routeName="school.examination.form.save-final"
      />

      {/* ── Inline Scoped CSS ─────────────────────────────────────────────── */}
      <style>{`
        .ex-page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.1rem; flex-wrap:wrap; }
        .ex-page-title { font-size:1.55rem; font-weight:800; color:#0F172A; line-height:1.2; }
        .ex-page-subtitle { font-size:.92rem; color:#64748B; margin-top:.35rem; }
        .ex-header-actions { display:flex; align-items:center; gap:.75rem; flex-shrink:0; flex-wrap:wrap; }
        .ex-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.72rem 1.1rem; border-radius:16px; font-size:.875rem; font-weight:700; cursor:pointer; transition:all .18s ease; border:1px solid transparent; text-decoration:none; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; white-space:nowrap; box-shadow:0 12px 26px rgba(148,163,184,.12); }
        .ex-btn svg { width:16px; height:16px; flex-shrink:0; }
        .ex-btn-primary { background:linear-gradient(135deg,#4F7CFF 0%,#6D9DFF 100%); color:#FFFFFF; }
        .ex-btn-primary:hover { transform:translateY(-1px); box-shadow:0 18px 30px rgba(79,124,255,.28); }
        .ex-btn-secondary { background:linear-gradient(135deg,#9B8CFF 0%,#7C6CFF 100%); color:#FFFFFF; }
        .ex-btn-secondary:hover { transform:translateY(-1px); box-shadow:0 18px 30px rgba(124,108,255,.22); }
        .ex-btn-gold { background:linear-gradient(135deg,#14B8A6 0%,#0EA5E9 100%); color:#FFFFFF; }
        .ex-btn-gold:hover { transform:translateY(-1px); box-shadow:0 18px 30px rgba(14,165,233,.24); }
        .ex-btn-ghost { background:#FFFFFF; color:#64748B; border-color:#E2E8F0; }
        .ex-btn-ghost:hover { background:#F8FBFF; color:#334155; border-color:#CBD5E1; }
        .ex-btn-outline { background:#FFFFFF; color:#334155; border-color:#D7E3F4; }
        .ex-btn-outline:hover { background:#F8FBFF; border-color:#BFDBFE; color:#0F172A; }
        .ex-btn-sm { padding:.58rem .95rem; font-size:.8125rem; border-radius:14px; }
        .ex-btn-divider { width:1px; height:30px; background:#E2E8F0; border-radius:9999px; }

        .ex-tabs { display:inline-flex; gap:.45rem; margin-bottom:1rem; padding:.45rem; background:rgba(255,255,255,.76); border:1px solid #E2E8F0; border-radius:20px; box-shadow:0 14px 30px rgba(148,163,184,.1); }
        .ex-tab { display:flex; align-items:center; gap:.5rem; padding:.72rem 1.15rem; font-size:.875rem; font-weight:700; color:#64748B; background:transparent; border:none; cursor:pointer; border-radius:16px; transition:all .18s ease; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; }
        .ex-tab:hover { color:#334155; background:#F8FBFF; }
        .ex-tab-active { color:#1D4ED8; background:linear-gradient(135deg,#EEF4FF 0%,#F8FBFF 100%); box-shadow:0 10px 20px rgba(79,124,255,.16); }
        .ex-tab-badge { display:inline-flex; align-items:center; justify-content:center; min-width:24px; height:20px; padding:0 .375rem; border-radius:9999px; font-size:.6875rem; font-weight:800; margin-left:.5rem; }
        .ex-tab-badge-draft { background:#E2E8F0; color:#475569; }
        .ex-tab-badge-final { background:#DBEAFE; color:#1E40AF; }
        .ex-tab-active .ex-tab-badge-draft,
        .ex-tab-active .ex-tab-badge-final,
        .ex-tab-badge-active { background:#1D4ED8; color:#FFFFFF; }

        .ex-stats-row { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:1rem; margin-bottom:1rem; }
        .ex-stat-card { border-radius:24px; padding:1.25rem 1.2rem; color:#FFFFFF; position:relative; overflow:hidden; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease; box-shadow:0 20px 40px rgba(148,163,184,.16); }
        .ex-stat-card::after { content:''; position:absolute; right:-18px; bottom:-24px; width:110px; height:110px; border-radius:50%; background:rgba(255,255,255,.1); }
        .ex-stat-hover:hover { transform:translateY(-3px); box-shadow:0 22px 42px rgba(15,23,42,.16); }
        .ex-stat-icon { width:36px; height:36px; background:rgba(255,255,255,0.18); padding:.5rem; border-radius:12px; margin-bottom:.7rem; box-shadow:inset 0 1px 0 rgba(255,255,255,.18); }
        .ex-stat-value { font-size:2rem; font-weight:800; margin-bottom:.25rem; line-height:1; }
        .ex-stat-label { font-size:.74rem; font-weight:700; opacity:.92; text-transform:uppercase; letter-spacing:.08em; }
        @keyframes ex-pulse { 0%,100% { box-shadow:0 20px 40px rgba(148,163,184,.16); } 50% { box-shadow:0 24px 44px rgba(249,115,22,.28); } }
        .ex-stat-pulse { animation:ex-pulse 2.4s infinite; }

        .ex-filters-card { background:linear-gradient(180deg,#FFFFFF 0%,#F8FBFF 100%); border:1px solid #E2E8F0; border-radius:24px; padding:1.1rem 1.25rem; margin-bottom:1rem; box-shadow:0 20px 45px rgba(148,163,184,.12); }
        .ex-filters-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:.85rem; align-items:end; }
        .ex-filter-group { display:flex; flex-direction:column; gap:.38rem; }
        .ex-filter-label { font-size:.72rem; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.08em; }
        .ex-search-wrap { position:relative; }
        .ex-search-icon { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); width:15px; height:15px; stroke:#94A3B8; pointer-events:none; }
        .ex-filter-input, .ex-filter-select { width:100%; padding:.8rem .95rem; border:1px solid #D7E3F4; border-radius:16px; font-size:.875rem; color:#0F172A; background:#FFFFFF; outline:none; transition:border-color .18s ease, box-shadow .18s ease; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; box-shadow:0 6px 18px rgba(148,163,184,.08); }
        .ex-filter-input:focus, .ex-filter-select:focus { border-color:#60A5FA; box-shadow:0 0 0 4px rgba(96,165,250,.12); }
        .ex-filter-input-search { padding-left:2.55rem; }
        .ex-filter-btns { display:flex; gap:.55rem; align-items:center; flex-wrap:wrap; }

        .ex-table-card { background:linear-gradient(180deg,#FFFFFF 0%,#FBFDFF 100%); border:1px solid #E2E8F0; border-radius:28px; overflow:hidden; box-shadow:0 24px 55px rgba(148,163,184,.12); }
        .ex-table-wrapper { overflow-x:auto; }
        .ex-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.875rem; }
        .ex-th { padding:.95rem 1rem; text-align:left; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#94A3B8; background:#F8FBFF; border-bottom:1px solid #E2E8F0; white-space:nowrap; }
        .ex-row { transition:background .14s ease; }
        .ex-row:hover td { background:#F8FBFF; }
        .ex-td { padding:.95rem 1rem; color:#334155; border-bottom:1px solid #EEF2F7; vertical-align:middle; }
        .ex-td-srl { color:#94A3B8; font-size:.8125rem; width:48px; }
        .ex-td-actions { text-align:right; width:180px; }
        .ex-student-name { font-weight:700; color:#0F172A; }
        .ex-student-father { font-size:.75rem; color:#64748B; margin-top:.125rem; }
        .ex-student-group { font-size:.75rem; color:#64748B; text-transform:capitalize; margin-left:.5rem; }
        .ex-enrollment-no { font-family:'Courier New',monospace; font-weight:700; color:#1D4ED8; }
        .ex-seat-no { font-family:'Courier New',monospace; font-weight:700; font-size:.9375rem; letter-spacing:.05em; color:#0F172A; }
        .ex-copy-btn { display:flex; align-items:center; gap:.35rem; padding:.42rem .7rem; border:1px solid #D7E3F4; background:#F8FBFF; border-radius:12px; cursor:pointer; transition:all .18s ease; box-shadow:0 8px 18px rgba(148,163,184,.08); }
        .ex-copy-btn:hover { background:#EEF4FF; border-color:#BFDBFE; }
        .ex-copy-btn svg { width:14px; height:14px; color:#3B82F6; }
        .ex-pending-badge { display:inline-flex; align-items:center; gap:.25rem; font-size:.75rem; font-weight:700; color:#64748B; background:#F8FAFC; border:1px solid #E2E8F0; padding:.28rem .6rem; border-radius:9999px; }
        .ex-badge { display:inline-flex; align-items:center; padding:.28rem .66rem; border-radius:9999px; font-size:.6875rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
        .ex-badge-blue { background:#DBEAFE; color:#1E40AF; }
        .ex-badge-amber { background:#FEF3C7; color:#92400E; }
        .ex-badge-green { background:#DCFCE7; color:#166534; }
        .ex-badge-gray { background:#E2E8F0; color:#475569; }
        .ex-actions { display:flex; align-items:center; justify-content:flex-end; gap:.35rem; }
        .ex-action-btn { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:12px; border:none; cursor:pointer; transition:all .18s ease; text-decoration:none; box-shadow:0 8px 18px rgba(148,163,184,.12); }
        .ex-action-btn svg { width:15px; height:15px; }
        .ex-action-btn:hover { transform:translateY(-1px); }
        .ex-action-view { background:#EEF2FF; color:#4F46E5; }
        .ex-action-view:hover { background:#E0E7FF; }
        .ex-action-edit { background:#ECFDF5; color:#059669; }
        .ex-action-edit:hover { background:#D1FAE5; }
        .ex-action-delete { background:#FEF2F2; color:#DC2626; }
        .ex-action-delete:hover { background:#FEE2E2; }
        .ex-action-finalize { background:#DBEAFE; color:#1D4ED8; }
        .ex-action-finalize:hover { background:#BFDBFE; }

        .ex-empty-cell { padding:0 !important; }
        .ex-empty { display:flex; flex-direction:column; align-items:center; padding:3.5rem 2rem; text-align:center; }
        .ex-empty-icon { width:72px; height:72px; background:linear-gradient(180deg,#F8FBFF 0%,#EEF4FF 100%); border:1px solid #D7E3F4; border-radius:22px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; color:#A5B4FC; box-shadow:0 14px 30px rgba(148,163,184,.12); }
        .ex-empty-icon svg { width:30px; height:30px; }
        .ex-empty-title { font-size:1.05rem; font-weight:800; color:#334155; margin-bottom:.45rem; }
        .ex-empty-desc { font-size:.875rem; color:#94A3B8; margin-bottom:1.35rem; max-width:30rem; }
        .ex-empty-actions { display:flex; gap:.75rem; flex-wrap:wrap; justify-content:center; }

        .ex-pagination { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-top:1px solid #EEF2F7; font-size:.875rem; background:#FCFEFF; }
        .ex-pagination-info { color:#64748B; }
        .ex-pagination-links { display:flex; gap:.35rem; flex-wrap:wrap; }
        .ex-page-btn { min-width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:12px; font-size:.8125rem; font-weight:700; text-decoration:none; color:#334155; transition:all .18s ease; padding:0 .65rem; background:#FFFFFF; border:1px solid transparent; }
        .ex-page-btn:hover:not(.ex-page-disabled):not(.ex-page-active) { background:#F8FBFF; border-color:#D7E3F4; }
        .ex-page-active { background:#1D4ED8; color:#FFFFFF !important; font-weight:800; box-shadow:0 10px 20px rgba(29,78,216,.2); }
        .ex-page-disabled { opacity:.35; pointer-events:none; }

        @media (max-width:1200px) { .ex-stats-row { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:960px) {
          .ex-filters-grid { grid-template-columns:1fr 1fr; }
          .ex-filter-wide { grid-column:span 2; }
          .ex-stats-row { grid-template-columns:repeat(2,1fr); }
        }
        @media (max-width:768px) {
          .ex-page-header { flex-direction:column; align-items:stretch; }
          .ex-header-actions { justify-content:flex-start; flex-wrap:wrap; }
          .ex-btn-divider { display:none; }
          .ex-tabs { display:flex; width:100%; }
          .ex-filters-grid { grid-template-columns:1fr; }
          .ex-filter-wide { grid-column:span 1; }
          .ex-stats-row { grid-template-columns:1fr; }
          .ex-pagination { flex-direction:column; gap:.75rem; align-items:flex-start; }
        }
      `}</style>
    </SchoolLayout>
  );
}
