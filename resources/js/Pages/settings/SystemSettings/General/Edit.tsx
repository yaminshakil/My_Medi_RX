import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useEffect } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'General Settings',
        href: '/settings/general',
    },
];

interface Props {
    settings: Record<string, string>;
    flash: Record<string, string>;
    countries: [];
}

export default function Edit({ settings, flash, countries }: Props) {
    const { data, setData, post, processing } = useForm({
        site_title: settings.site_title || '',
        currency: settings.currency || '',
        currency_symbol: settings.currency_symbol || '',
        timezone: settings.timezone || 'Asia/Dhaka',
        site_country_code: settings.site_country_code || '+880', // Mobile code
        records_per_page: settings.records_per_page || '10',
        currency_format: settings.currency_format || '1,234.56',
        site_mobile: settings.site_mobile || '',
        site_email: settings.site_email || '',
        site_address: settings.site_address || '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('settings.system.general.update'));
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
            <Head title="General Settings" />
            <ToastContainer />
            <div className="overflow-hidden bg-white shadow-md sm:rounded-lg">
                <form role="form" onSubmit={submit} className="mx-auto w-full space-y-6 p-4">
                    {/* 4 Column Grid */}
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {/* Site Title */}
                        <div>
                            <Label htmlFor="site_title" className="after:text-red-500 after:content-['*']">
                                Site Title{' '}
                            </Label>
                            <Input id="site_title" value={data.site_title} onChange={(e) => setData('site_title', e.target.value)} />
                        </div>

                        {/* Currency */}
                        <div>
                            <Label htmlFor="currency" className="after:text-red-500 after:content-['*']">
                                Currency
                            </Label>
                            <Input id="currency" value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
                        </div>

                        {/* Currency Symbol */}
                        <div>
                            <Label htmlFor="currency_symbol" className="after:text-red-500 after:content-['*']">
                                Currency Symbol
                            </Label>
                            <Input id="currency_symbol" value={data.currency_symbol} onChange={(e) => setData('currency_symbol', e.target.value)} />
                        </div>

                        {/* Currency Format Dropdown */}
                        <div>
                            <Label htmlFor="currency_format" className="after:text-red-500 after:content-['*']">
                                Currency Format
                            </Label>
                            <Select value={data.currency_format} onValueChange={(value) => setData('currency_format', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select Format" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="text_symbol">Show Currency Text and Symbol Both</SelectItem>
                                    <SelectItem value="text_only">Show Currency Text Only</SelectItem>
                                    <SelectItem value="symbol_only">Show Currency Symbol Only</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Timezone Dropdown */}
                        <div>
                            <Label htmlFor="timezone" className="after:text-red-500 after:content-['*']">
                                Timezone
                            </Label>
                            <Select value={data.timezone} onValueChange={(value) => setData('timezone', value)}>
                                <SelectTrigger data-testid="timezone-trigger">
                                    <SelectValue placeholder="Select Timezone" />
                                </SelectTrigger>
                                <SelectContent data-testid="timezone-options">
                                    {countries.map((country) => (
                                        <SelectItem key={country.timezone} value={country.timezone}>
                                            {country.name} {country.timezone}
                                        </SelectItem>
                                    ))}
                                    <SelectItem value="UTC">UTC</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Mobile Country Code Dropdown */}
                        <div>
                            <Label htmlFor="site_country_code" className="after:text-red-500 after:content-['*']">
                                Mobile Country Code
                            </Label>
                            <Select value={data.site_country_code} onValueChange={(value) => setData('site_country_code', value)}>
                                <SelectTrigger data-testid="country-code-trigger">
                                    <SelectValue placeholder="Select Country Code" />
                                </SelectTrigger>
                                <SelectContent data-testid="country-code-options">
                                    {countries.map((country) => (
                                        <SelectItem key={country.phone_code} value={country.phone_code}>
                                            {country.code} {country.name} ({country.phone_code})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Records Per Page */}
                        <div>
                            <Label htmlFor="records_per_page" className="after:text-red-500 after:content-['*']">
                                Records Per Page
                            </Label>
                            <Input
                                id="records_per_page"
                                type="number"
                                value={data.records_per_page}
                                onChange={(e) => setData('records_per_page', e.target.value)}
                            />
                        </div>

                        {/* Site Mobile */}
                        <div>
                            <Label htmlFor="site_mobile" className="after:text-red-500 after:content-['*']">
                                Mobile
                            </Label>
                            <Input id="site_mobile" type="text" value={data.site_mobile} onChange={(e) => setData('site_mobile', e.target.value)} />
                        </div>

                        {/* Site Email */}
                        <div>
                            <Label htmlFor="site_email" className="after:text-red-500 after:content-['*']">
                                Email
                            </Label>
                            <Input id="site_email" type="email" value={data.site_email} onChange={(e) => setData('site_email', e.target.value)} />
                        </div>

                        {/* Site Adress */}
                        <div>
                            <Label htmlFor="site_address" className="after:text-red-500 after:content-['*']">
                                Adress
                            </Label>
                            <Input
                                id="site_address"
                                type="text"
                                value={data.site_address}
                                onChange={(e) => setData('site_address', e.target.value)}
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
