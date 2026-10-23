import AppLayout from '@/Layouts/app-layout';
import { Head } from "@inertiajs/react";
import HighlightForm from "./Form";
import { type BreadcrumbItem } from '@/types';

type Highlight = {
    id: number;
    week: number;
    fruit_name: string;
    approx_length_cm: number | null;
    approx_weight_grams: number | null;
    description: string;
    source_reference: string | null;
    source_version: string | null;
    is_published: boolean;
};

export default function Edit({ highlight }: { highlight: Highlight }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Weekly Highlight', href: '/admin/pregnancy/highlights' }];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Week ${highlight.week} Highlight`} />

            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Edit Weekly Highlight
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Update week {highlight.week} content.
                    </p>
                </div>

                <HighlightForm highlight={highlight} />
            </div>
        </AppLayout>
    );
}
