import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const toastContainerId = 'post-action-notifications';

function hasFlashMessage(flash: unknown): boolean {
    if (typeof flash !== 'object' || flash === null) {
        return false;
    }

    const message = (flash as { message?: unknown }).message;

    if (typeof message !== 'object' || message === null) {
        return false;
    }

    const { success, error } = message as {
        success?: unknown;
        error?: unknown;
    };

    return (
        (typeof success === 'string' && success.trim().length > 0) ||
        (typeof error === 'string' && error.trim().length > 0)
    );
}

export default function GlobalPostNotifications() {
    useEffect(() => {
        let isPostVisit = false;

        const removeStartListener = router.on('start', (event) => {
            isPostVisit = event.detail.visit.method === 'post';
        });

        const removeSuccessListener = router.on('success', (event) => {
            if (
                !isPostVisit ||
                hasFlashMessage(event.detail.page.props.flash)
            ) {
                return;
            }

            toast.success('Request completed successfully.', {
                containerId: toastContainerId,
            });
        });

        const removeFinishListener = router.on('finish', () => {
            isPostVisit = false;
        });

        return () => {
            removeStartListener();
            removeSuccessListener();
            removeFinishListener();
        };
    }, []);

    return <ToastContainer containerId={toastContainerId} />;
}
