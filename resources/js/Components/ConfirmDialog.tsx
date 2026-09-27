import { Button } from '@/Components/ui/button';

export function ConfirmDialog({ message, onConfirm, onCancel, isOpen, title }) {
    if (!isOpen) return null;

    return (
        <div className="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="w-full max-w-sm rounded-lg bg-white p-6 shadow-lg">
                <h2 className="mb-4 text-xl font-semibold">{title}</h2>
                <div className="mb-6">
                    <svg
                        className="mx-auto mb-4 h-12 w-12 text-red-400 dark:text-red-200"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 20 20"
                    >
                        <path
                            stroke="currentColor"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />
                    </svg>
                    <div dangerouslySetInnerHTML={{ __html: message }} />
                </div>
                <div className="flex justify-end space-x-4">
                    <Button
                        onClick={onCancel}
                        className="ms-3 cursor-pointer rounded-lg border border-gray-200 bg-gray-200 px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-300 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        No, cancel
                    </Button>
                    <Button
                        onClick={onConfirm}
                        className="inline-flex cursor-pointer items-center rounded-lg bg-red-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300 focus:outline-none dark:focus:ring-red-800"
                    >
                        Yes, I'm sure
                    </Button>
                </div>
            </div>
        </div>
    );
}
