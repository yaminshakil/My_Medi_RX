import { Button } from '@/Components/ui/button';
import { useCallback, useEffect } from 'react';

export function CommonModal({
    children,
    title = 'Modal Title',
    onConfirm,
    onCancel,
    isOpen,
    onClose,
    confirmText = 'Save',
    cancelText = 'Cancel',
    size = 'lg',
}) {
    // Close on outside click
    const handleBackdropClick = useCallback(
        (e) => {
            if (e.target === e.currentTarget && onClose) {
                onClose();
            }
        },
        [onClose],
    );

    // Close on ESC key
    useEffect(() => {
        if (!isOpen) return;

        const handleEscape = (e) => {
            if (e.key === 'Escape' && onClose) {
                onClose();
            }
        };

        document.addEventListener('keydown', handleEscape);
        return () => document.removeEventListener('keydown', handleEscape);
    }, [isOpen, onClose]);

    const sizeClasses = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
    };

    if (!isOpen) return null;

    return (
        <div
            data-testid="common-modal"
            className="bg-opacity-50 fixed inset-0 z-50 flex min-h-screen items-center justify-center bg-black p-4"
            onClick={handleBackdropClick}
        >
            <div className={`w-full rounded-lg bg-white shadow-xl ${sizeClasses[size]} flex max-h-[90vh] flex-col overflow-hidden`}>
                {/* Header */}
                <div className="flex items-center justify-between border-b border-gray-200 p-4">
                    <h3 className="text-lg font-semibold text-gray-900">{title}</h3>

                    {onClose && (
                        <button data-testid="common-modal-close" onClick={onClose} className="text-gray-400 transition-colors hover:text-gray-600">
                            ✕
                        </button>
                    )}
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto p-4">{children}</div>

                {/* Footer */}
                <div className="flex flex-col justify-end gap-2 border-t border-gray-200 bg-gray-50 p-4 sm:flex-row-reverse">
                    <Button data-testid="common-modal-confirm" onClick={onConfirm} className="rounded-md px-4 py-2 font-medium text-white">
                        {confirmText}
                    </Button>

                    <Button data-testid="common-modal-cancel" onClick={onCancel || onClose} className="rounded-md px-4 py-2 font-medium">
                        {cancelText}
                    </Button>
                </div>
            </div>
        </div>
    );
}
