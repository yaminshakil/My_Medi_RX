import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Color Settings',
        href: '/settings/Color/Edit',
    },
];

interface Props {
    appSettings: Record<string, string>;
    flash: Record<string, string>;
}

export default function Edit({ appSettings, flash }: Props) {
    const { data, setData, post, processing } = useForm({
        site_base_color: appSettings.site_base_color || '#84cc16',
        site_button_bgcolor: appSettings.site_button_bgcolor || '#84cc16',
        site_button_hover_bgcolor: appSettings.site_button_hover_bgcolor || '#84cc16',
        site_header_top_bgcolor: appSettings.site_header_top_bgcolor || '#84cc16',
        site_footer_bgcolor: appSettings.site_footer_bgcolor || '#84cc16',
        site_footer_bottom_bgcolor: appSettings.site_footer_bottom_bgcolor || '#84cc16',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('settings.system.color.update'));
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
            <Head title="Color Settings" />
            <ToastContainer />
            <div className="overflow-hidden bg-white shadow-md sm:rounded-lg">
                <form role="form" onSubmit={submit} className="mx-auto w-full space-y-4 p-4">
                    {/* 4 Column Grid */}
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {/* Site Title */}

                        {/* Site Base Color */}
                        <div>
                            <Label htmlFor="site_base_color" className="after:text-red-500 after:content-['*']">
                                Site Base Color
                            </Label>
                            <Input
                                id="site_base_color"
                                type="color"
                                value={data.site_base_color}
                                onChange={(e) => setData('site_base_color', e.target.value)}
                            />
                        </div>

                        {/* Site Base Button Color */}
                        <div>
                            <Label htmlFor="site_button_bgcolor" className="after:text-red-500 after:content-['*']">
                                Site Button Color
                            </Label>
                            <Input
                                id="site_button_bgcolor"
                                type="color"
                                value={data.site_button_bgcolor}
                                onChange={(e) => setData('site_button_bgcolor', e.target.value)}
                            />
                        </div>

                        {/* Site Base Button Hover Color */}
                        <div>
                            <Label htmlFor="site_button_hover_bgcolor" className="after:text-red-500 after:content-['*']">
                                Site Button Hover Color
                            </Label>
                            <Input
                                id="site_button_hover_bgcolor"
                                type="color"
                                value={data.site_button_hover_bgcolor}
                                onChange={(e) => setData('site_button_hover_bgcolor', e.target.value)}
                            />
                        </div>

                        {/* Site Header Top Bg Color */}
                        <div>
                            <Label htmlFor="site_header_top_bgcolor" className="after:text-red-500 after:content-['*']">
                                Site Header Top Color
                            </Label>
                            <Input
                                id="site_header_top_bgcolor"
                                type="color"
                                value={data.site_header_top_bgcolor}
                                onChange={(e) => setData('site_header_top_bgcolor', e.target.value)}
                            />
                        </div>

                        {/* Site Footer Color */}
                        <div>
                            <Label htmlFor="site_footer_bgcolor" className="after:text-red-500 after:content-['*']">
                                Site Footer Color
                            </Label>
                            <Input
                                id="site_footer_bgcolor"
                                type="color"
                                value={data.site_footer_bgcolor}
                                onChange={(e) => setData('site_footer_bgcolor', e.target.value)}
                            />
                        </div>

                        {/* Site Footer Bottom Color */}
                        <div>
                            <Label htmlFor="site_footer_bottom_bgcolor" className="after:text-red-500 after:content-['*']">
                                Site Footer Bottom Color
                            </Label>
                            <Input
                                id="site_footer_bottom_bgcolor"
                                type="color"
                                value={data.site_footer_bottom_bgcolor}
                                onChange={(e) => setData('site_footer_bottom_bgcolor', e.target.value)}
                            />
                        </div>
                    </div>

                    {/* Submit */}
                    <div className="flex justify-start gap-2">
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
