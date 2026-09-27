import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Seo Settings',
        href: '/settings/seo',
    },
];

export default function SeoSettings() {
    const { appSettings, flash } = usePage().props as { appSettings: Record<string, string>; flash: Record<string, string> };

    const { data, setData, post, processing } = useForm({
        seo_title: appSettings.seo_title || '',
        seo_description: appSettings.seo_description || '',
        seo_keywords: appSettings.seo_keywords || '',
        seo_og_title: appSettings.seo_og_title || '',
        seo_og_description: appSettings.seo_og_description || '',
        seo_og_image: null as File | null,
        seo_twitter_title: appSettings.seo_twitter_title || '',
        seo_twitter_description: appSettings.seo_twitter_description || '',
        seo_twitter_image: null as File | null,
    });

    const [ogImagePreview, setOgImagePreview] = useState(appSettings.seo_og_image ? `/storage/${appSettings.seo_og_image}` : null);
    const [twitterImagePreview, setTwitterImagePreview] = useState(
        appSettings.seo_twitter_image ? `/storage/${appSettings.seo_twitter_image}` : null,
    );

    useEffect(() => {
        if (data.seo_og_image) setOgImagePreview(URL.createObjectURL(data.seo_og_image));
    }, [data.seo_og_image]);

    useEffect(() => {
        if (data.seo_twitter_image) setTwitterImagePreview(URL.createObjectURL(data.seo_twitter_image));
    }, [data.seo_twitter_image]);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('settings.system.seo.update'), { forceFormData: true });
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
            <Head title="Seo Settings" />
            <ToastContainer />
            <div className="overflow-hidden bg-white shadow-md sm:rounded-lg">
                <form role="form" onSubmit={submit} className="space-y-4 p-4">
                    {/* SEO Meta */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <Label htmlFor="seo_title">SEO Title</Label>
                            <Input
                                id="seo_title"
                                type="text"
                                className="w-full rounded border p-2"
                                value={data.seo_title}
                                onChange={(e) => setData('seo_title', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_description">SEO Description</Label>
                            <Textarea
                                id="seo_description"
                                className="w-full rounded border p-2"
                                value={data.seo_description}
                                onChange={(e) => setData('seo_description', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_keywords">SEO Keywords</Label>
                            <Input
                                id="seo_keywords"
                                type="text"
                                className="w-full rounded border p-2"
                                value={data.seo_keywords}
                                onChange={(e) => setData('seo_keywords', e.target.value)}
                            />
                        </div>
                    </div>

                    {/* Open Graph */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <Label htmlFor="seo_og_title">OG Title</Label>
                            <Input
                                id="seo_og_title"
                                type="text"
                                className="w-full rounded border p-2"
                                value={data.seo_og_title}
                                onChange={(e) => setData('seo_og_title', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_og_description">OG Description</Label>
                            <Textarea
                                id="seo_og_description"
                                className="w-full rounded border p-2"
                                value={data.seo_og_description}
                                onChange={(e) => setData('seo_og_description', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_og_image">OG Image</Label>
                            <Input
                                id="seo_og_image"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('seo_og_image', e.target.files?.[0] || null)}
                            />
                            {ogImagePreview && <img src={ogImagePreview} alt="OG Preview" className="mt-2 h-32" />}
                        </div>
                    </div>

                    {/* Twitter Card */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <Label htmlFor="seo_twitter_title">Twitter Title</Label>
                            <Input
                                id="seo_twitter_title"
                                type="text"
                                className="w-full rounded border p-2"
                                value={data.seo_twitter_title}
                                onChange={(e) => setData('seo_twitter_title', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_twitter_description">Twitter Description</Label>
                            <Textarea
                                id="seo_twitter_description"
                                className="w-full rounded border p-2"
                                value={data.seo_twitter_description}
                                onChange={(e) => setData('seo_twitter_description', e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="seo_twitter_image">Twitter Image</Label>
                            <Input
                                id="seo_twitter_image"
                                type="file"
                                accept="image/*"
                                onChange={(e) => setData('seo_twitter_image', e.target.files?.[0] || null)}
                            />
                            {twitterImagePreview && <img src={twitterImagePreview} alt="Twitter Preview" className="mt-2 h-32" />}
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
                            Save SEO Settings
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
