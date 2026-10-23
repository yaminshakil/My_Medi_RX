import AppLayout from "@/Layouts/app-layout";
import { Head } from "@inertiajs/react";
import TipForm from "./Form";
import { type BreadcrumbItem } from '@/types';

type Tip = {
    id: number;
    title: string;
    description: string;
    icon: string;
    color_hex: string;
    trimester: number | null;
    week_start: number;
    week_end: number;
    sort_order: number;
    is_published: boolean;
    source_reference: string | null;
};

export default function Edit({ tip }: { tip: Tip }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Pregnancy Tip', href: '/admin/pregnancy/tips/edit' }];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Tip: ${tip.title}`} />
            <div className="space-y-6 p-6">
                <h1 className="text-2xl font-semibold">Edit Pregnancy Tip</h1>
                <TipForm tip={tip} />
            </div>
        </AppLayout>
    );
}
