import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cache Settings',
        href: '/admin/settings/system/cachesettings',
    },
];

export default function Index() {
    const { flash } = usePage().props as { flash: Record<string, string> };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const settingsMenu = [
        {
            title: 'Clear Route:Cache',
            icon: RefreshCw,
            href: route('settings.system.routecachesettings.clear'),
        },
        {
            title: 'Clear View:Cache',
            icon: RefreshCw,
            href: route('settings.system.viewcachesettings.clear'),
        },
        {
            title: 'Clear Config:Cache',
            icon: RefreshCw,
            href: route('settings.system.configcachesettings.clear'),
        },
        {
            title: 'Clear Event:Cache',
            icon: RefreshCw,
            href: route('settings.system.eventcachesettings.clear'),
        },
        {
            title: 'Clear All Kind of Cache',
            icon: RefreshCw,
            href: route('settings.system.allcachesettings.clear'),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Cache Settings" />
            <ToastContainer />
            <div className="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
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
                                    <p className="text-sm text-gray-500">Cache {item.title.toLowerCase()} here.</p>
                                </CardContent>
                            </Card>
                        </Link>
                    );
                })}
            </div>
        </AppLayout>
    );
}
