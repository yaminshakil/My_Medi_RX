import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Bell, FileText, Globe, Image, Monitor, Palette, Settings } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'System Settings',
        href: '/system-setting',
    },
];

export default function Index() {
    const settingsMenu = [
        {
            title: 'General Setting',
            icon: Settings,
            href: route('settings.system.general.edit'),
        },
        {
            title: 'Site Identity',
            icon: Image,
            href: route('settings.system.site_identity.edit'),
        },
        {
            title: 'Color Settings',
            icon: Palette,
            href: route('settings.system.color.edit'),
        },
        {
            title: 'System Configuration',
            icon: Monitor,
            href: route('settings.system.systemconfig.index'),
        },
        {
            title: 'Notification Setting',
            icon: Bell,
            //href: route('email-templates.index'),
        },
        {
            title: 'SEO Configuration',
            icon: Globe,
            href: route('settings.system.seo.edit'),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="System settings" />
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
