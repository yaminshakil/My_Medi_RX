import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/app-layout';
import DepartmentForm from './DepartmentForm';
import type { Department, HospitalOption } from './types';

export default function Edit({ department, hospitals }: { department: Department; hospitals: HospitalOption[] }) {
    return (
        <AppLayout>
            <Head title={`Edit ${department.name}`} />
            <div className="mx-auto max-w-5xl space-y-6 p-6">
                <div><h1 className="text-2xl font-semibold">Edit Department</h1><p className="text-sm text-muted-foreground">{department.name}</p></div>
                <DepartmentForm department={department} hospitals={hospitals}
                    submitUrl={route('department.update', department.id)} method="put" />
            </div>
        </AppLayout>
    );
}
