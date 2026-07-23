import React from 'react';
import { router } from '@inertiajs/react';
import { createPortal } from 'react-dom';
import type { Student } from '@/types';

interface SaveFinalDialogProps {
  student: Student | null;
  onCancel: () => void;
  onSuccess: () => void;
  routeName?: string;
  /** When set, used instead of student.id (e.g. exam form id). */
  recordId?: number;
}

/**
 * Confirmation dialog for the quick "Save Final" action from the enrollment/exam form list.
 * Sends a PATCH request to finalize the record. Shows error toast if validation fails server-side.
 */
export function SaveFinalDialog({ student, onCancel, onSuccess, routeName = 'school.students.save-final', recordId }: SaveFinalDialogProps) {
  const [loading, setLoading] = React.useState(false);

  if (!student) return null;

  function handleConfirm() {
    if (!student) return;
    setLoading(true);

    router.patch(
      route(routeName, recordId ?? student.id),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          setLoading(false);
          onSuccess();
        },
        onError: () => {
          setLoading(false);
        },
        onFinish: () => {
          setLoading(false);
        },
      }
    );
  }

  const dialog = (
    <div className="sfd-backdrop" onMouseDown={(e) => { if (e.target === e.currentTarget && !loading) onCancel(); }}>
      <div className="sfd-box">
        <div className="sfd-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
          </svg>
        </div>
        <h3 className="sfd-title">Confirm as Final?</h3>
        <p className="sfd-desc">
          Confirm <strong>{student.full_name}</strong>'s enrollment as final?
          It will become eligible for challan generation and should no longer need major edits.
        </p>
        {loading && (
          <div className="sfd-loading">
            <div className="sfd-spinner" />
            <span>Validating and saving…</span>
          </div>
        )}
        <div className="sfd-actions">
          <button type="button" className="sfd-btn sfd-btn-ghost" onClick={onCancel} disabled={loading}>Cancel</button>
          <button type="button" className="sfd-btn sfd-btn-navy" onClick={handleConfirm} disabled={loading}>
            {loading ? 'Processing…' : 'Confirm Final'}
          </button>
        </div>
      </div>

      <style>{`
        .sfd-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:110;display:flex;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(2px)}
        .sfd-box{background:#fff;border-radius:16px;padding:1.75rem 1.5rem;width:100%;max-width:400px;text-align:center;box-shadow:0 24px 64px rgba(0,0,0,.18)}
        .sfd-icon{width:52px;height:52px;background:#DBEAFE;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#1B3A6B}
        .sfd-icon svg{width:24px;height:24px}
        .sfd-title{font-size:1.0625rem;font-weight:700;color:#111827;margin-bottom:.5rem}
        .sfd-desc{font-size:.875rem;color:#6B7280;line-height:1.5;margin-bottom:1.25rem}
        .sfd-loading{display:flex;align-items:center;justify-content:center;gap:.5rem;margin-bottom:1rem;font-size:.8125rem;color:#6B7280}
        .sfd-spinner{width:16px;height:16px;border:2px solid #E5E7EB;border-top-color:#1B3A6B;border-radius:50%;animation:sfd-spin .6s linear infinite}
        @keyframes sfd-spin{to{transform:rotate(360deg)}}
        .sfd-actions{display:flex;justify-content:center;gap:.75rem}
        .sfd-btn{display:inline-flex;align-items:center;gap:.375rem;padding:.5625rem 1.125rem;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;transition:all .15s ease;border:none;font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .sfd-btn:disabled{opacity:.5;cursor:not-allowed}
        .sfd-btn-navy{background:#1B3A6B;color:#fff}.sfd-btn-navy:hover:not(:disabled){background:#162d54}
        .sfd-btn-ghost{background:transparent;color:#6B7280;border:1.5px solid #E5E7EB}.sfd-btn-ghost:hover:not(:disabled){background:#F3F4F6}
      `}</style>
    </div>
  );

  return createPortal(dialog, document.body);
}
