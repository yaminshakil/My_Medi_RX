
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import { Button } from '@/Components/ui/button';

export default function Create() {
    return (
        <AppLayout>
            <Head title="Create Menstrual Cycle Profile" />

            <div className="mx-auto max-w-2xl space-y-5 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Create Menstrual Cycle Profile
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Profiles are created when a user first accesses
                        menstrual cycle tracking.
                    </p>
                </div>

                <p className="rounded-md border p-4 text-sm">
                    A profile is automatically created for an authenticated
                    user by the cycle status API. Use the Profiles page to
                    manage existing profiles.
                </p>

                <Button asChild variant="outline">
                    <Link href={route('admin.menstrual-cycle.profiles.index')}>
                        Back to profiles
                    </Link>
                </Button>
            </div>
        </AppLayout>
    );
}
