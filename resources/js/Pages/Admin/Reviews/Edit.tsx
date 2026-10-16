// Edit.tsx
import { Head } from '@inertiajs/react';
import ReviewForm from './Form';

export default function Edit({ review, users }: { review: any; users: any[] }) {
    return (
        <>
            <Head title="Edit Review" />
            <ReviewForm review={review} users={users} />
        </>
    );
}
