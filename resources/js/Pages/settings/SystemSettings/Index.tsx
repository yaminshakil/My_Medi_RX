import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import AppLayout from "@/Layouts/app-layout";
import { type BreadcrumbItem } from "@/types";
import { Head, Link, usePage } from "@inertiajs/react";
import {
    Bell,
    FileText,
    Globe,
    Image,
    Monitor,
    Palette,
    Settings,
} from "lucide-react";



export default function Index() {
    const { translations } = usePage<AppPageProps>().props;
    console.log(translations.common.system_settings);
    const breadcrumbs: BreadcrumbItem[] = [
    {
        title: translations.common.system_settings,
        href: "/system-setting",
    },
];
    const settingsMenu = [
        {
            title: translations.common.general_settings,
            icon: Settings,
            href: route("settings.system.general.edit"),
        },
        {
            title: translations.common.site_identity,
            icon: Image,
            href: route("settings.system.site_identity.edit"),
        },
        {
            title: translations.common.color_settings,
            icon: Palette,
            href: route("settings.system.color.edit"),
        },
        {
            title: translations.common.system_configuration,
            icon: Monitor,
            href: route("settings.system.systemconfig.index"),
        },
        {
            title: translations.common.notification_settings,
            icon: Bell,
            href: route("settings.system.email-templates.index"),
        },
        {
            title: translations.common.seo_configuration,
            icon: Globe,
            href: route("settings.system.seo.edit"),
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
                                    <CardTitle className="text-lg">
                                        {item.title}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-gray-500">
                                        {translations.common.configure} {item.title.toLowerCase()}{" "}
                                        {translations.common.here}.
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                    );
                })}
            </div>
        </AppLayout>
    );
}
