import React from 'react';
import { AppModal } from '@/components/shared/AppModal';
import { AppButton } from '@/components/shared/AppButton';

interface ConfirmDialogProps {
  open: boolean;
  title: string;
  message: React.ReactNode;
  confirmLabel?: string;
  cancelLabel?: string;
  variant?: 'danger' | 'primary';
  loading?: boolean;
  disabled?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}

export function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  variant = 'primary',
  loading = false,
  disabled = false,
  onConfirm,
  onCancel,
}: ConfirmDialogProps) {
  return (
    <AppModal open={open} onClose={onCancel} title={title}>
      <div className="px-6 py-4 space-y-4">
        <div className="text-sm text-gray-600">{message}</div>
        <div className="flex justify-end gap-2">
          <AppButton type="button" variant="secondary" onClick={onCancel} disabled={loading}>
            {cancelLabel}
          </AppButton>
          <AppButton
            type="button"
            variant={variant === 'danger' ? 'danger' : 'primary'}
            onClick={onConfirm}
            loading={loading}
            disabled={disabled}
          >
            {confirmLabel}
          </AppButton>
        </div>
      </div>
    </AppModal>
  );
}
