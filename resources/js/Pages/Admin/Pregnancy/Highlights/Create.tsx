import AppLayout from '@/Layouts/app-layout';
import { Head } from "@inertiajs/react";
import HighlightForm from "./Form";
import { type BreadcrumbItem } from '@/types';


export default function Create() {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Weekly Highlight', href: '/admin/pregnancy/highlights' }];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Pregnancy Highlight" />

            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Create Weekly Highlight
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Add a pregnancy week, size comparison, and development
                        description.
                    </p>
                </div>

                <HighlightForm />
            </div>
        </AppLayout>
    );
}
