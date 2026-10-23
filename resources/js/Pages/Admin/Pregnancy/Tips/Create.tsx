import AppLayout from "@/Layouts/app-layout";
import { Head } from "@inertiajs/react";
import TipForm from "./Form";
import { type BreadcrumbItem } from '@/types';

export default function Create() {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Pregnancy Tip', href: '/admin/pregnancy/tips/create' }];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Pregnancy Tip" />
            <div className="space-y-6 p-6">
                <h1 className="text-2xl font-semibold">Create Pregnancy Tip</h1>
                <TipForm />
            </div>
        </AppLayout>
    );
}
