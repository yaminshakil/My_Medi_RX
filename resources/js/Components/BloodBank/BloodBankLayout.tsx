import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'; // Breeze layout; change this one import if yours lives elsewhere
import { Head, Link, usePage } from '@inertiajs/react';
import { cx } from './ui';
import AppLayout from '@/Layouts/app-layout';

// [label, route name, pattern used to highlight the active tab]
const nav = [
    ['Overview', 'blood-bank.dashboard', 'blood-bank.dashboard'],
    ['Blood units', 'blood-bank.bags.index', 'blood-bank.bags.*'],
    ['Issue log', 'blood-bank.issues.index', 'blood-bank.issues.*'],
    ['Emergency requests', 'blood-bank.emergency.index', 'blood-bank.emergency.*'],
    ['Donors', 'blood-bank.donors.index', 'blood-bank.donors.*'],
];

export default function BloodBankLayout({ title, actions, children }) {
    const { flash } = usePage().props;

    return (
        <AppLayout
            header={
                <div className="space-y-3">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h2 className="text-xl font-semibold text-slate-800">{title}</h2>
                        {actions}
                    </div>
                    <nav className="-mb-px flex gap-1 overflow-x-auto" aria-label="Blood bank sections">
                        {nav.map(([label, name, pattern]) => (
                            <Link
                                key={name}
                                href={route(name)}
                                className={cx(
                                    'whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium',
                                    route().current(pattern)
                                        ? 'border-rose-800 text-rose-900'
                                        : 'border-transparent text-slate-500 hover:text-slate-800'
                                )}
                            >
                                {label}
                            </Link>
                        ))}
                    </nav>
                </div>
            }
        >
            <Head title={title} />
            <div className="py-6">
                <div className="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div role="status" className="rounded-md bg-teal-50 px-4 py-3 text-sm text-teal-900 ring-1 ring-teal-200">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div role="alert" className="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-900 ring-1 ring-rose-200">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </div>
            </div>
        </AppLayout>
    );
}
