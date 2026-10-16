import { Head } from '@inertiajs/react';
import ReviewForm from './Form';
import AppLayout from '@/Layouts/app-layout';

export default function Create({ users }: { users: any[] }) {
    return (
        <AppLayout>
            <Head title="Create Review" />
            <ReviewForm users={users} />
        </AppLayout>
    );
}
