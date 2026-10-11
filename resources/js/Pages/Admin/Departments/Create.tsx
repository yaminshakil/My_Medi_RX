import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import DepartmentForm from './DepartmentForm';
import type { HospitalOption } from './types';

export default function Create({ hospitals }: { hospitals: HospitalOption[] }) {
    return (
        <AppLayout>
            <Head title="Create Department" />
            <div className="mx-auto max-w-5xl space-y-6 p-6">
                <div><h1 className="text-2xl font-semibold">Create Department</h1><p className="text-sm text-muted-foreground">Add a department to a hospital.</p></div>
                <DepartmentForm hospitals={hospitals} submitUrl={route('department.store')} />
            </div>
        </AppLayout>
    );
}
