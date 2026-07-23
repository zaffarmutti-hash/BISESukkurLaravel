import { router } from '@inertiajs/react';
import { useState } from 'react';
import axios from 'axios';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { AppSelect } from '@/components/shared/AppSelect';
import { AppInput } from '@/components/shared/AppInput';
import type { AcademicYear } from '@/types';

interface PreChecks {
  pending_enrollment_verification: number;
  pending_exam_verification: number;
  pending_enrollment_numbers: number;
  missing_exam_forms: number;
  total_students: number;
}

interface TransitionProps {
  activeYear?: AcademicYear | null;
  years: AcademicYear[];
  preChecks: PreChecks;
}

type StepStatus = 'pending' | 'running' | 'success' | 'failed';

interface TransitionStep {
  id: number;
  label: string;
  description: string;
  status: StepStatus;
  resultMessage?: string;
}

export default function AcademicYearTransition({ activeYear, years, preChecks }: TransitionProps) {
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [nextYearId, setNextYearId] = useState('');
  const [confirmationText, setConfirmationText] = useState('');
  
  // Runner State
  const [running, setRunning] = useState(false);
  const [progress, setProgress] = useState(0);
  const [steps, setSteps] = useState<TransitionStep[]>([
    { id: 1, label: 'Promote Eligible Candidates', description: 'Promotes regulars from Part I to Part II for the new year', status: 'pending' },
    { id: 2, label: 'Expire Completed/Inactive Records', description: 'Deactivates completed Part II students and 2-year inactive candidates', status: 'pending' },
    { id: 3, label: 'Swap Active Academic Year', description: 'Toggles system-wide active year setting and closes windows', status: 'pending' },
  ]);

  const blockers = [
    { label: 'Enrollment payments awaiting verification', count: preChecks.pending_enrollment_verification },
    { label: 'Exam payments awaiting verification', count: preChecks.pending_exam_verification },
    { label: 'Students missing enrollment numbers', count: preChecks.pending_enrollment_numbers },
    { label: 'Students with no exam form started', count: preChecks.missing_exam_forms },
  ];

  const hasBlockers = blockers.some((b) => b.count > 0);

  function updateStepStatus(stepId: number, status: StepStatus, message?: string) {
    setSteps(prev => prev.map(s => s.id === stepId ? { ...s, status, resultMessage: message } : s));
  }

  async function executeTransition() {
    setConfirmOpen(false);
    setRunning(true);
    setProgress(10);

    let promoted = 0;
    let expired = 0;

    // Step 1: Promote
    try {
      updateStepStatus(1, 'running');
      const res1 = await axios.post(route('superadmin.academic-year-transition.promote'), { next_year_id: nextYearId });
      promoted = res1.data.count;
      updateStepStatus(1, 'success', `Successfully promoted ${promoted} regular candidates.`);
      setProgress(40);
    } catch (err: any) {
      updateStepStatus(1, 'failed', err.response?.data?.error ?? 'Promotion failed.');
      setRunning(false);
      return;
    }

    // Step 2: Expire
    try {
      updateStepStatus(2, 'running');
      const res2 = await axios.post(route('superadmin.academic-year-transition.expire'));
      expired = res2.data.count;
      updateStepStatus(2, 'success', `Flagged ${expired} candidates as completed or expired.`);
      setProgress(70);
    } catch (err: any) {
      updateStepStatus(2, 'failed', err.response?.data?.error ?? 'Expiration failed.');
      setRunning(false);
      return;
    }

    // Step 3: Swap Active Year
    try {
      updateStepStatus(3, 'running');
      const res3 = await axios.post(route('superadmin.academic-year-transition.swap'), {
        next_year_id: nextYearId,
        promoted_count: promoted,
        expired_count: expired,
      });
      updateStepStatus(3, 'success', 'Active operational year updated successfully.');
      setProgress(100);

      setTimeout(() => {
        router.visit(res3.data.redirect);
      }, 1500);
    } catch (err: any) {
      updateStepStatus(3, 'failed', err.response?.data?.error ?? 'Year swap failed.');
      setRunning(false);
    }
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Academic Year Control / Year Transition</span>}>
      <PageHeader
        title="Academic Year Transition"
        subtitle="Step-by-step rollover wizard to advance active operational years"
      />

      <div className="card mb-6 border-amber-200 bg-amber-50">
        <div className="card-body">
          <p className="text-sm text-amber-900">
            Current active year: <strong>{activeYear?.label ?? 'None'}</strong>.
            Transitioning updates the operational session for every school tenant board-wide.
          </p>
        </div>
      </div>

      {!running ? (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <div className="card">
            <div className="card-header"><h2 className="card-title">Pre-Transition Checks</h2></div>
            <div className="card-body space-y-3">
              {blockers.map((item) => (
                <div key={item.label} className="flex items-center justify-between text-sm">
                  <span>{item.label}</span>
                  <StatusBadge
                    label={String(item.count)}
                    variant={item.count > 0 ? 'warning' : 'success'}
                    pulse={item.count > 0}
                  />
                </div>
              ))}
              {hasBlockers && (
                <p className="text-xs text-amber-700 pt-2">
                  Outstanding items are shown for awareness. Resolve critical backlogs before transitioning when possible.
                </p>
              )}
            </div>
          </div>

          <div className="card">
            <div className="card-header"><h2 className="card-title">Select Target Year</h2></div>
            <div className="card-body space-y-4">
              <AppSelect
                label="Transition system to"
                value={nextYearId}
                onChange={(e) => setNextYearId(e.target.value)}
                placeholder=""
              >
                {years.filter((y) => y.id !== activeYear?.id).map((y) => (
                  <option key={y.id} value={y.id}>{y.label}</option>
                ))}
              </AppSelect>
              <button
                type="button"
                className="btn btn-danger w-full justify-center"
                disabled={!nextYearId}
                onClick={() => setConfirmOpen(true)}
              >
                Begin Transition
              </button>
            </div>
          </div>
        </div>
      ) : (
        <div className="card mb-6 border-indigo-200">
          <div className="card-header bg-indigo-50/50 flex items-center justify-between">
            <h2 className="card-title text-indigo-950 font-bold">Execution Progress</h2>
            <span className="text-sm font-semibold text-indigo-700">{progress}% Completed</span>
          </div>
          <div className="card-body space-y-6">
            {/* Progress Bar */}
            <div className="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
              <div
                className="bg-indigo-600 h-3 rounded-full transition-all duration-500 ease-out"
                style={{ width: `${progress}%` }}
              />
            </div>

            {/* Checklist */}
            <div className="space-y-4">
              {steps.map((step) => (
                <div key={step.id} className="flex gap-4 items-start border-b border-slate-50 pb-3 last:border-none">
                  <div className="mt-1">
                    {step.status === 'pending' && <span className="text-slate-300 text-lg">○</span>}
                    {step.status === 'running' && <span className="animate-spin inline-block text-indigo-600 text-lg">↻</span>}
                    {step.status === 'success' && <span className="text-emerald-600 text-lg">✓</span>}
                    {step.status === 'failed' && <span className="text-red-600 text-lg">✗</span>}
                  </div>
                  <div className="flex-1">
                    <div className="text-sm font-bold text-slate-800">{step.label}</div>
                    <div className="text-xs text-slate-400">{step.description}</div>
                    {step.resultMessage && (
                      <div className={`text-xs mt-1 font-semibold ${step.status === 'success' ? 'text-emerald-700' : 'text-red-700'}`}>
                        {step.resultMessage}
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog
        open={confirmOpen}
        title="Confirm Academic Year Transition"
        variant="danger"
        message={
          <div className="space-y-3">
            <p>This action is irreversible. Type <strong>TRANSITION</strong> to confirm.</p>
            <AppInput
              value={confirmationText}
              onChange={(e) => setConfirmationText(e.target.value)}
              placeholder="Type TRANSITION"
            />
          </div>
        }
        confirmLabel="Execute Rollover"
        onConfirm={executeTransition}
        disabled={confirmationText !== 'TRANSITION'}
        onCancel={() => setConfirmOpen(false)}
      />
    </SuperAdminLayout>
  );
}
