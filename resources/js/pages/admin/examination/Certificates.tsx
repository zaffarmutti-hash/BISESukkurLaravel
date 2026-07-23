import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';

import { AppButton } from '@/components/shared/AppButton';
import { AppInput } from '@/components/shared/AppInput';
import { AppSelect } from '@/components/shared/AppSelect';
import { ConfirmDialog } from '@/components/shared/ConfirmDialog';
import { usePermissions } from '@/hooks/usePermissions';
import type { Paginated, Student, AcademicYear } from '@/types';

interface CertificateRow {
  id: number;
  level: 'ssc' | 'hsc';
  verification_token: string;
  verification_url?: string;
  total_percentage: number;
  overall_grade: string;
  division: string;
  is_issued: boolean;
  issued_at: string;
  student?: Student & { school?: { name: string; code: string } };
  academic_year?: AcademicYear;
}

interface CertificatesProps {
  certificates: Paginated<CertificateRow>;
  stats: {
    total: number;
    ssc: number;
    hsc: number;
  };
  activeYear?: AcademicYear | null;
  filters: {
    level?: string;
    search?: string;
  };
}

export default function Certificates({ certificates, stats, activeYear, filters }: CertificatesProps) {
  const { can } = usePermissions();
  const [showConfirm, setShowConfirm] = useState(false);
  const [targetLevel, setTargetLevel] = useState<'ssc' | 'hsc'>('ssc');



  function triggerGenerate(level: 'ssc' | 'hsc') {
    setTargetLevel(level);
    setShowConfirm(true);
  }

  function handleConfirm() {
    router.post(route('superadmin.examination.certificates.generate'), { level: targetLevel }, {
      onSuccess: () => setShowConfirm(false),
    });
  }

  function handleFilter(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    router.get(route('superadmin.examination.certificates.index'), Object.fromEntries(formData), { preserveState: true });
  }

  return (
    <SuperAdminLayout breadcrumb={<span className="text-sm text-gray-500">Examination Hub / Certificates</span>}>
      <PageHeader
        title="Official Certificate Issuance"
        subtitle="Manage, generate, and verify candidates certificates for SSC and HSC exams completion"
      />

      {activeYear ? (
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">Total Certificates Issued</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.total}</div>
            </div>
          </div>
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">SSC Certificates</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.ssc}</div>
            </div>
          </div>
          <div className="card bg-slate-50 border-slate-200">
            <div className="card-body">
              <span className="text-xs font-semibold text-slate-500 uppercase">HSC Certificates</span>
              <div className="text-2xl font-bold text-slate-800 mt-1">{stats.hsc}</div>
            </div>
          </div>
          <div className="card bg-amber-50 border-amber-200 flex flex-col justify-center p-3">
            <div className="flex gap-2 w-full">
              {can('examination.view') && (
                <>
                  <AppButton variant="primary" size="sm" className="flex-1 justify-center" onClick={() => triggerGenerate('ssc')}>
                    Generate SSC
                  </AppButton>
                  <AppButton variant="secondary" size="sm" className="flex-1 justify-center" onClick={() => triggerGenerate('hsc')}>
                    Generate HSC
                  </AppButton>
                </>
              )}
            </div>
          </div>
        </div>
      ) : (
        <div className="alert alert-warning mb-6">
          Active operational year is missing. Please configure it under Academic Years.
        </div>
      )}

      <form onSubmit={handleFilter} className="card mb-6 bg-slate-50 border-slate-200">
        <div className="card-body grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <AppInput
            label="Search Candidate"
            name="search"
            defaultValue={filters.search ?? ''}
            placeholder="Search by name or enrollment number..."
          />
          <AppSelect
            label="Filter Level"
            name="level"
            defaultValue={filters.level ?? ''}
            placeholder=""
          >
            <option value="">All Levels</option>
            <option value="ssc">SSC Level</option>
            <option value="hsc">HSC Level</option>
          </AppSelect>
          <button type="submit" className="btn btn-primary h-[42px] justify-center">
            Search Certificates
          </button>
        </div>
      </form>

      <div className="card">
        <div className="card-body overflow-x-auto p-0">
          <table className="app-table w-full text-sm">
            <thead>
              <tr>
                <th className="pl-6">Verification Token</th>
                <th>Candidate Name & Roll</th>
                <th>School Code & Name</th>
                <th>Exam Level</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Division</th>
                <th className="pr-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {certificates.data.map((c) => (
                <tr key={c.id}>
                  <td className="pl-6 font-mono text-xs text-slate-500 font-semibold">{c.verification_token.substring(0, 12)}...</td>
                  <td>
                    <div className="font-semibold text-slate-800">{c.student?.full_name}</div>
                    <div className="text-xs text-slate-400 font-mono">Enroll: {c.student?.enrollment_number}</div>
                  </td>
                  <td>
                    <div className="text-sm font-medium text-slate-900">{c.student?.school?.name}</div>
                    <div className="text-xs text-slate-400">Code: {c.student?.school?.code}</div>
                  </td>
                  <td>
                    <span className="badge badge-info text-xs uppercase">{c.level}</span>
                  </td>
                  <td className="font-semibold text-slate-900">
                    {Number(c.total_percentage).toFixed(2)}%
                  </td>
                  <td className="font-bold text-indigo-900">{c.overall_grade}</td>
                  <td className="text-xs font-semibold text-slate-700">{c.division}</td>
                  <td className="pr-6 text-right">
                    <a
                      href={`/verify-certificate/${c.verification_token}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn btn-secondary btn-sm"
                    >
                      Print Preview ⎙
                    </a>
                  </td>
                </tr>
              ))}
              {certificates.data.length === 0 && (
                <tr>
                  <td colSpan={8} className="text-center py-8 text-slate-400 text-sm">
                    No issued certificates found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmDialog
        open={showConfirm}
        title="Confirm Certificate Generation"
        message={
          <div className="space-y-2">
            <p>You are about to run the automated certificate generator for all passing <strong>{targetLevel.toUpperCase()}</strong> candidates.</p>
            <p className="text-xs text-indigo-700">
              The service will scan candidate results, check pass status across all subjects, compute overall grade points, and issue secure verification tokens.
            </p>
          </div>
        }
        confirmLabel="Execute Generation"
        onConfirm={handleConfirm}
        onCancel={() => setShowConfirm(false)}
      />
    </SuperAdminLayout>
  );
}
