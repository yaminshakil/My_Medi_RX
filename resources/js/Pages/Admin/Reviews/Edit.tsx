// Edit.tsx
import { Head } from '@inertiajs/react';
import ReviewForm from './Form';
import AppLayout from '@/Layouts/app-layout';

export default function Edit({ review, users }: { review: any; users: any[] }) {
    return (
        <AppLayout>
            <Head title="Edit Review" />
            <ReviewForm review={review} users={users} />
        </AppLayout>
    );
}
