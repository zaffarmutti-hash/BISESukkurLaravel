import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import SchoolLayout from '@/layouts/SchoolLayout';
import type { Invoice, Challan, Paginated, AcademicYear } from '@/types';

// ─── Types ────────────────────────────────────────────────────────────────────

interface InvoiceListProps {
  invoices:    Paginated<Invoice & { challan?: Challan }>;
  activeYear?: AcademicYear | null;
  filters?: {
    status?: string;
    type?:   string;
  };
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function statusBadge(status: string): { label: string; css: string } {
  const map: Record<string, { label: string; css: string }> = {
    draft:     { label: 'Draft',     css: 'il-badge-gray' },
    submitted: { label: 'Submitted', css: 'il-badge-amber' },
    confirmed: { label: 'Confirmed', css: 'il-badge-green' },
    rejected:  { label: 'Rejected',  css: 'il-badge-red' },
  };
  return map[status] ?? { label: status, css: 'il-badge-gray' };
}

function formatDate(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleDateString('en-PK', {
      day: '2-digit', month: 'short', year: 'numeric',
    });
  } catch {
    return dateStr;
  }
}

// ─── Component ────────────────────────────────────────────────────────────────

/**
 * Invoice / Challan list with download links.
 * Page 6 of the migration — replaces the stub InvoiceList.vue.
 * File: resources/js/pages/school/Challan/InvoiceList.tsx
 */
export default function InvoiceList({
  invoices,
  activeYear,
  filters: initialFilters = {},
}: InvoiceListProps) {
  const [statusFilter, setStatusFilter] = useState(initialFilters.status ?? '');
  const [typeFilter,   setTypeFilter]   = useState(initialFilters.type   ?? '');
  const hasActiveFilters = Boolean(statusFilter || typeFilter);

  function applyFilters() {
    const params: Record<string, string> = {};
    if (statusFilter) params.status = statusFilter;
    if (typeFilter)   params.type   = typeFilter;
    router.get(route('school.enrollment.challans'), params, { preserveState: true, replace: true });
  }

  function clearFilters() {
    setStatusFilter('');
    setTypeFilter('');
    router.get(route('school.enrollment.challans'), {}, { preserveState: true, replace: true });
  }

  const meta = invoices.meta;

  return (
    <SchoolLayout
      breadcrumb={
        <nav className="flex items-center gap-1.5 text-sm">
          <span className="text-gray-400">School</span>
          <span className="text-gray-300">/</span>
          <span className="text-gray-700 font-medium">Invoices</span>
        </nav>
      }
    >
      {/* ── Page Header ─────────────────────────────────────────────────── */}
      <div className="il-header">
        <div>
          <h1 className="il-title">Invoices &amp; Challans</h1>
          <p className="il-subtitle">
            {meta?.total ?? invoices.data?.length ?? 0} invoices&nbsp;·&nbsp;
            {activeYear?.label ?? 'No active year'}
          </p>
        </div>
      </div>

      {/* ── Filters ─────────────────────────────────────────────────────── */}
      <div className="il-filters">
        <select
          className="il-filter-select"
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="confirmed">Confirmed</option>
          <option value="rejected">Rejected</option>
        </select>

        <select
          className="il-filter-select"
          value={typeFilter}
          onChange={(e) => setTypeFilter(e.target.value)}
        >
          <option value="">All Types</option>
          <option value="enrollment">Enrollment Challan</option>
          <option value="examination">Examination Challan</option>
        </select>

        <button type="button" className="il-filter-btn" onClick={applyFilters}>
          Apply Filters
        </button>
        {hasActiveFilters && (
          <button type="button" className="il-filter-btn il-filter-btn-ghost" onClick={clearFilters}>
            Clear
          </button>
        )}
      </div>

      {/* ── Table ───────────────────────────────────────────────────────── */}
      <div className="il-card">
        <div className="il-table-wrap">
          <table className="il-table">
            <thead>
              <tr>
                <th className="il-th">Invoice No.</th>
                <th className="il-th">Type</th>
                <th className="il-th">Students</th>
                <th className="il-th">Amount</th>
                <th className="il-th">Status</th>
                <th className="il-th">Date</th>
                <th className="il-th il-th-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {invoices.data?.map((invoice) => {
                const { label, css } = statusBadge(invoice.status);
                return (
                  <tr key={invoice.id} className="il-row">
                    <td className="il-td">
                      <span className="il-invoice-no">{invoice.invoice_number}</span>
                    </td>
                    <td className="il-td">
                      <div className="flex flex-col gap-1 items-start">
                        <span className={`il-badge ${invoice.challan?.type === 'enrollment' ? 'il-badge-blue' : 'il-badge-purple'}`}>
                          {invoice.challan?.type === 'enrollment' ? 'Enrollment' : 'Examination'}
                        </span>
                        {invoice.fee_phase === 'grace' && (
                          <span className="text-[10px] font-bold px-1.5 py-0.5 rounded" style={{ background: '#FEF3C7', color: '#92400E', border: '1px solid #FCD34D' }}>
                            Late Surcharge
                          </span>
                        )}
                      </div>
                    </td>
                    <td className="il-td il-td-num">
                      {invoice.challan?.total_students ?? '—'}
                    </td>
                    <td className="il-td">
                      <span className="il-amount">Rs. {invoice.amount.toLocaleString()}</span>
                      {invoice.late_fee_surcharge_paisas && invoice.late_fee_surcharge_paisas > 0 ? (
                        <div className="text-[10px] text-amber-700 font-semibold mt-0.5">
                          (inc. Rs. {(invoice.late_fee_surcharge_paisas / 100).toLocaleString()} late fee)
                        </div>
                      ) : null}
                    </td>
                    <td className="il-td">
                      <span className={`il-badge ${css}`}>{label}</span>
                    </td>
                    <td className="il-td il-td-meta">{formatDate(invoice.created_at)}</td>
                    <td className="il-td il-td-actions">
                      <div className="il-actions">
                        {/* Download Challan PDF */}
                        <a
                          href={route('school.documents.challan.print', invoice.challan_id)}
                          target="_blank"
                          rel="noreferrer"
                          className="il-action-btn il-action-download"
                          title="Download Challan PDF"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                          </svg>
                          Challan
                        </a>

                        {/* Download Student List PDF */}
                        <a
                          href={route('school.documents.challan.students', invoice.challan_id)}
                          target="_blank"
                          rel="noreferrer"
                          className="il-action-btn il-action-list"
                          title="Download Student List"
                        >
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                          </svg>
                          List
                        </a>
                      </div>
                    </td>
                  </tr>
                );
              })}

              {!invoices.data?.length && (
                <tr>
                  <td colSpan={7} className="il-empty">
                    <svg className="w-10 h-10 text-gray-300 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                      <rect x="2" y="5" width="20" height="14" rx="2"/>
                      <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                    <p className="text-sm text-gray-400">No invoices found.</p>
                    {!activeYear?.is_enrollment_open && (
                      <p className="text-xs text-gray-400 mt-1">Enrollment window is currently closed.</p>
                    )}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {(meta?.last_page ?? 0) > 1 && (
          <div className="il-pagination">
            <span className="il-page-info">
              Showing {meta?.from}–{meta?.to} of {meta?.total}
            </span>
            <div className="il-page-links">
              {meta?.links.map((link) => (
                <Link
                  key={link.label}
                  href={link.url ?? '#'}
                  preserveScroll
                  className={[
                    'il-page-btn',
                    link.active ? 'il-page-active' : '',
                    !link.url  ? 'il-page-disabled' : '',
                  ].join(' ')}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          </div>
        )}
      </div>

      <style>{`
        .il-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
        .il-title{font-size:1.55rem;font-weight:800;color:#0F172A;line-height:1.2}
        .il-subtitle{font-size:.92rem;color:#64748B;margin-top:.35rem}
        .il-filters{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;padding:1rem 1.1rem;background:linear-gradient(180deg,#FFFFFF 0%,#F8FBFF 100%);border:1px solid #E2E8F0;border-radius:22px;box-shadow:0 20px 45px rgba(148,163,184,.12)}
        .il-filter-select{padding:.8rem .95rem;border:1px solid #D7E3F4;border-radius:16px;font-size:.875rem;color:#0F172A;background:#fff;outline:none;cursor:pointer;transition:border-color .18s ease,box-shadow .18s ease;box-shadow:0 6px 18px rgba(148,163,184,.08)}
        .il-filter-select:focus{border-color:#60A5FA;box-shadow:0 0 0 4px rgba(96,165,250,.12)}
        .il-filter-btn{padding:.72rem 1rem;background:linear-gradient(135deg,#4F7CFF 0%,#6D9DFF 100%);color:#fff;border:1px solid transparent;border-radius:16px;font-size:.875rem;font-weight:700;cursor:pointer;transition:all .18s ease;font-family:'Inter',ui-sans-serif,system-ui,sans-serif;box-shadow:0 12px 26px rgba(79,124,255,.2)}
        .il-filter-btn:hover{transform:translateY(-1px)}
        .il-filter-btn-ghost{background:#FFFFFF;color:#475569;border-color:#D7E3F4;box-shadow:0 10px 22px rgba(148,163,184,.08)}
        .il-filter-btn-ghost:hover{background:#F8FBFF}
        .il-card{background:linear-gradient(180deg,#FFFFFF 0%,#FBFDFF 100%);border:1px solid #E2E8F0;border-radius:28px;overflow:hidden;box-shadow:0 24px 55px rgba(148,163,184,.12)}
        .il-table-wrap{overflow-x:auto}
        .il-table{width:100%;border-collapse:separate;border-spacing:0;font-size:.875rem}
        .il-th{padding:.95rem 1rem;text-align:left;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;background:#F8FBFF;border-bottom:1px solid #E2E8F0;white-space:nowrap}
        .il-th-right{text-align:right}
        .il-row:hover td{background:#F8FBFF}
        .il-td{padding:.95rem 1rem;color:#334155;border-bottom:1px solid #EEF2F7;vertical-align:middle}
        .il-td-num{color:#334155;font-variant-numeric:tabular-nums}
        .il-td-meta{color:#94A3B8;font-size:.8125rem;white-space:nowrap}
        .il-td-actions{text-align:right}
        .il-table tbody tr:last-child td{border-bottom:none}
        .il-invoice-no{font-family:'Courier New',monospace;font-weight:700;font-size:.8125rem;color:#1D4ED8;letter-spacing:.03em}
        .il-amount{font-weight:700;color:#0F172A}
        .il-badge{display:inline-flex;align-items:center;padding:.28rem .66rem;border-radius:9999px;font-size:.6875rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
        .il-badge-gray{background:#E2E8F0;color:#475569}
        .il-badge-blue{background:#DBEAFE;color:#1E40AF}
        .il-badge-purple{background:#F3E8FF;color:#7C3AED}
        .il-badge-amber{background:#FEF3C7;color:#92400E}
        .il-badge-green{background:#DCFCE7;color:#166534}
        .il-badge-red{background:#FEE2E2;color:#991B1B}
        .il-actions{display:flex;align-items:center;justify-content:flex-end;gap:.45rem;flex-wrap:wrap}
        .il-action-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem .8rem;border-radius:14px;font-size:.75rem;font-weight:700;text-decoration:none;transition:all .18s ease;white-space:nowrap;box-shadow:0 10px 20px rgba(148,163,184,.1)}
        .il-action-btn:hover{transform:translateY(-1px)}
        .il-action-btn svg{width:13px;height:13px;flex-shrink:0}
        .il-action-download{background:#EEF2FF;color:#4F46E5}.il-action-download:hover{background:#E0E7FF}
        .il-action-list{background:#ECFDF5;color:#059669}.il-action-list:hover{background:#D1FAE5}
        .il-empty{padding:4rem 2rem;text-align:center;color:#94A3B8}
        .il-pagination{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-top:1px solid #EEF2F7;font-size:.875rem;background:#FCFEFF}
        .il-page-info{color:#64748B}
        .il-page-links{display:flex;gap:.35rem;flex-wrap:wrap}
        .il-page-btn{min-width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:.8125rem;font-weight:700;text-decoration:none;color:#334155;transition:all .18s ease;padding:0 .65rem;background:#FFFFFF;border:1px solid transparent}
        .il-page-btn:hover:not(.il-page-disabled):not(.il-page-active){background:#F8FBFF;border-color:#D7E3F4}
        .il-page-active{background:#1D4ED8;color:#fff !important;font-weight:800;box-shadow:0 10px 20px rgba(29,78,216,.2)}
        .il-page-disabled{opacity:.35;pointer-events:none}
        @media(max-width:640px){.il-pagination{flex-direction:column;align-items:flex-start;gap:.75rem}}
      `}</style>
    </SchoolLayout>
  );
}
