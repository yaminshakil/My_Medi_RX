import { PlaceholderPattern } from '@/Components/ui/placeholder-pattern';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarDays, Pill } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function PatientDashboard() {
    const { patientinfo } = usePage().props;
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <Link href={route('appointments.index')} className="block h-full transition hover:opacity-90">
                            <div className="flex h-full w-full flex-col items-center justify-center rounded-lg bg-[var(--base-color)] p-4 text-center shadow">
                                <div className="flex justify-center text-sm font-medium text-white">Appointments</div>
                                <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{patientinfo.totalappoinments}</div>
                                <div className="mt-2 flex justify-center text-sm font-medium text-gray-500">
                                    <CalendarDays className="text-xl font-medium text-white" />
                                </div>
                                <div className="mt-4 flex justify-center text-center text-sm font-medium break-words whitespace-normal text-white">
                                    View Appointments List
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <Link href={route('appointments.new')} className="block h-full transition hover:opacity-90">
                            <div className="flex h-full w-full flex-col items-center justify-center rounded-lg bg-[var(--base-color)] p-4 text-center shadow">
                                <div className="flex justify-center text-sm font-medium text-white">New Appointments</div>
                                <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{patientinfo.newappointments}</div>
                                <div className="mt-2 flex justify-center text-sm font-medium text-gray-500">
                                    <CalendarDays className="text-xl font-medium text-white" />
                                </div>
                                <div className="mt-4 flex justify-center text-center text-sm font-medium break-words whitespace-normal text-white">
                                    View Appointments List
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <Link href={route('prescriptions.index')} className="block h-full transition hover:opacity-90">
                            <div className="flex h-full w-full flex-col items-center justify-center rounded-lg bg-[var(--base-color)] p-4 text-center shadow">
                                <div className="flex justify-center text-sm font-medium text-white">Prescriptions</div>
                                <div className="mt-1 flex justify-center text-3xl font-semibold text-white">{patientinfo.totalprescriptions}</div>
                                <div className="mt-2 flex justify-center text-sm font-medium text-gray-500">
                                    <Pill className="text-xl font-medium text-white" />
                                </div>
                                <p className="mt-4 flex justify-center text-center text-sm font-medium break-words whitespace-normal text-white">
                                    View Prescriptions List
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
