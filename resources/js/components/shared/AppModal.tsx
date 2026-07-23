import React, { Fragment } from 'react';
import { Dialog, Transition } from '@headlessui/react';

interface AppModalProps {
  open: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
  /** Max-width class e.g. 'max-w-lg', 'max-w-2xl' */
  size?: string;
}

/**
 * Accessible modal dialog using Headless UI.
 * Drop-in React replacement for AppModal.vue.
 *
 * Usage:
 *   <AppModal open={isOpen} onClose={() => setIsOpen(false)} title="Confirm">
 *     ...content...
 *   </AppModal>
 */
export function AppModal({
  open,
  onClose,
  title,
  children,
  size = 'max-w-lg',
}: AppModalProps) {
  return (
    <Transition appear show={open} as={Fragment}>
      <Dialog as="div" className="relative z-50" onClose={onClose}>
        {/* Overlay */}
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-200"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-150"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </Transition.Child>

        {/* Panel */}
        <div className="fixed inset-0 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4">
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-200"
              enterFrom="opacity-0 scale-95"
              enterTo="opacity-100 scale-100"
              leave="ease-in duration-150"
              leaveFrom="opacity-100 scale-100"
              leaveTo="opacity-0 scale-95"
            >
              <Dialog.Panel
                className={`w-full ${size} rounded-2xl bg-white shadow-xl ring-1 ring-black/5`}
              >
                {title && (
                  <div className="px-6 pt-5 pb-4 border-b border-gray-100">
                    <Dialog.Title className="text-base font-semibold text-gray-900">
                      {title}
                    </Dialog.Title>
                  </div>
                )}
                {children}
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition>
  );
}
