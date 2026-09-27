import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Site Identity',
        href: '/settings/site-identity',
    },
];

export default function Index() {
    const { appSettings, flash } = usePage().props as { appSettings: Record<string, string>; flash: Record<string, string> };

    const { data, setData, post, processing } = useForm({
        logo: null as File | null,
        favicon: null as File | null,
        navbar_logo: null as File | null,
        dashboard_logo: null as File | null,
    });

    // Local preview URLs
    const [logoPreview, setLogoPreview] = useState<string | null>(appSettings.logo ? `/storage/${appSettings.logo}` : null);
    const [navBarLogoPreview, setNavBarLogoPreview] = useState<string | null>(appSettings.navbar_logo ? `/storage/${appSettings.navbar_logo}` : null);
    const [dashboardLogoPreview, setDashboardLogoPreview] = useState<string | null>(
        appSettings.dashboard_logo ? `/storage/${appSettings.dashboard_logo}` : null,
    );
    const [faviconPreview, setFaviconPreview] = useState<string | null>(appSettings.favicon ? `/storage/${appSettings.favicon}` : null);

    // Update preview when file is selected
    useEffect(() => {
        if (data.logo) {
            setLogoPreview(URL.createObjectURL(data.logo));
        }
    }, [data.logo]);

    useEffect(() => {
        if (data.navbar_logo) {
            setNavBarLogoPreview(URL.createObjectURL(data.navbar_logo));
        }
    }, [data.navbar_logo]);

    useEffect(() => {
        if (data.dashboard_logo) {
            setDashboardLogoPreview(URL.createObjectURL(data.dashboard_logo));
        }
    }, [data.dashboard_logo]);

    useEffect(() => {
        if (data.favicon) {
            setFaviconPreview(URL.createObjectURL(data.favicon));
        }
    }, [data.favicon]);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('settings.system.site_identity.update'), { forceFormData: true });
    }

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Logo And Favicon Settings" />
            <ToastContainer />
            <div className="overflow-hidden bg-white p-4 shadow-md sm:rounded-lg">
                <form role="form" onSubmit={submit} className="mx-auto w-full space-y-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {/* Logo Upload */}
                        <div>
                            <Label htmlFor="logo" className="mb-1 block">
                                Logo
                            </Label>
                            <Input id="logo" type="file" accept="image/*" onChange={(e) => setData('logo', e.target.files?.[0] || null)} />
                            {logoPreview && <img src={logoPreview} alt="Logo Preview" className="mt-2 h-20 rounded border object-contain p-1" />}
                        </div>

                        {/* Navbar Logo Upload */}
                        <div>
                            <Label htmlFor="navbar_logo" className="mb-1 block">
                                Navbar Logo
                            </Label>
                            <Input
                                id="navbar_logo"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('navbar_logo', e.target.files?.[0] || null)}
                            />
                            {navBarLogoPreview && (
                                <img src={navBarLogoPreview} alt="Navbar Logo Preview" className="mt-2 h-20 rounded border object-contain p-1" />
                            )}
                        </div>

                        {/* Dashboard Logo Upload */}
                        <div>
                            <Label htmlFor="dashboard_logo" className="mb-1 block">
                                Dashboard Logo
                            </Label>
                            <Input
                                id="dashboard_logo"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('dashboard_logo', e.target.files?.[0] || null)}
                            />
                            {dashboardLogoPreview && (
                                <img
                                    src={dashboardLogoPreview}
                                    alt="Dashboard Logo Preview"
                                    className="mt-2 h-20 rounded border object-contain p-1"
                                />
                            )}
                        </div>

                        {/* Favicon Upload */}
                        <div>
                            <Label htmlFor="favicon" className="mb-1 block">
                                Favicon
                            </Label>
                            <Input id="favicon" type="file" accept="image/*" onChange={(e) => setData('favicon', e.target.files?.[0] || null)} />
                            {faviconPreview && (
                                <img src={faviconPreview} alt="Favicon Preview" className="mt-2 h-10 w-10 rounded border object-contain p-1" />
                            )}
                        </div>
                    </div>
                    <div className="mt-4 flex justify-start gap-2">
                        <Button
                            type="button"
                            onClick={() => {
                                router.get(route('settings.system.index'));
                            }}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Save Settings
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
