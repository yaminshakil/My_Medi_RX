// Edit.tsx
import { Head } from '@inertiajs/react';
import ReviewForm from './Form';
import AppLayout from '@/Layouts/app-layout';

export default function Edit({ review, users, doctors }: { review: any; users: any[], doctors: any[] }) {
    return (
        <AppLayout>
            <Head title="Edit Review" />
            <ReviewForm review={review} users={users} doctors={doctors} />
        </AppLayout>
    );
}
