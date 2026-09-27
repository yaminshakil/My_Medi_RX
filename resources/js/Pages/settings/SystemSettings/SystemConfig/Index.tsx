import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Database, RefreshCw } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System Configuration Settings',
        href: '/admin/settings/system/systemconfig',
    },
];

export default function Index() {
    const settingsMenu = [
        {
            title: 'Cache Settings',
            icon: RefreshCw,
            href: route('settings.system.cachesettings.index'),
        },
        {
            title: 'DB Backup',
            icon: Database,
            href: route('settings.system.backup.index'),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="System settings" />
            <div className="ml-auto p-4">
                <Button onClick={() => window.history.back()} className="text-white">
                    <ArrowLeft className="h-4 w-4" /> Back
                </Button>
            </div>

            <div className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3">
                {settingsMenu.map((item, index) => {
                    const Icon = item.icon;
                    return (
                        <Link key={index} href={item.href}>
                            <Card className="h-full cursor-pointer transition-all hover:shadow-lg">
                                <CardHeader className="flex flex-row items-center gap-3">
                                    <Icon className="h-6 w-6 text-[var(--base-color)]" />
                                    <CardTitle className="text-lg">{item.title}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-gray-500">Configure {item.title.toLowerCase()} here.</p>
                                </CardContent>
                            </Card>
                        </Link>
                    );
                })}
            </div>
        </AppLayout>
    );
}
