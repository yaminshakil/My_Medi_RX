// Create.tsx
import { Head } from '@inertiajs/react';
import ReviewForm from './Form';

export default function Create({ users }: { users: any[] }) {
    return (
        <>
            <Head title="Create Review" />
            <ReviewForm users={users} />
        </>
    );
}
