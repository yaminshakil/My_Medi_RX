import { Head } from '@inertiajs/react';
import ReviewForm from './Form';
import AppLayout from '@/Layouts/app-layout';

export default function Create({ users, doctors }: { users: any[], doctors: any[] }) {
    return (
        <AppLayout>
            <Head title="Create Review" />
            <ReviewForm users={users} doctors={doctors} />
        </AppLayout>
    );
}
