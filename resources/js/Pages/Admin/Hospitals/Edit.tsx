import React, { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/app-layout';
import { Head, usePage, useForm, router } from '@inertiajs/react';

import { Hospital, ArrowLeft, Save, Upload, Image as ImageIcon } from 'lucide-react';
import { Loader2 } from 'lucide-react';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';

import GeoLocation from '@/Components/GeoLocation';
import BannerCropper from '@/Components/BannerCropper';
import LogoCropper from '@/Components/LogoCropper';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

export default function Edit(props) {
    const { hospital, hospitalTypes } = usePage().props;
    const [loadingHospitals, setLoadingHospitals] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        hospital_name: hospital.data[0].hospital_name || '',
        hospital_type_id: hospital.data[0].hospital_type_id || '',
        hospital_logo: hospital.data[0].hospital_logo || '',
        address: hospital.data[0].address || '',
        hospital_description: hospital.data[0].hospital_description || '',
        hospital_url: hospital.data[0].hospital_url || '',
        mobile_number: hospital.data[0].mobile_number || '',
        phone_number: hospital.data[0].phone_number || '',
        thana_id: hospital.data[0].thana_id || '',
        district_id: hospital.data[0].district_id || '',
        division_id: hospital.data[0].division_id || '',
        sort_order: hospital.data[0].sort_order || '',
        status: hospital.data[0].status || 0,
        prev_hospital_logo: hospital.data[0].hospital_logo || '',
        banner_url: null,
        prev_banner_url: hospital.data[0].banner_url || '',
        banner_crop_data: hospital.data[0].banner_crop_data,
        registration_no: hospital.data[0].registration_no || '',
        service_time: hospital.data[0].service_time || '',
        organization_notice: hospital.data[0].organization_notice || '',
        latitude: hospital.data[0].latitude || '00.000000',
        longitude: hospital.data[0].longitude || '00.000000',
        verification_status: hospital.data[0].verification_status || 'pending',
        verification_note: hospital.data[0].verification_note || '',
    });


    useEffect(() => {
        return () => {
            reset('hospital_name', 'address', 'hospital_description', 'hospital_url', 'mobile_number', 'phone_number', 'sort_order', 'thana', 'district', 'status');
        };
    }, []);

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('hospital.update', hospital.data[0].id));
    };

    const onSelectHandleChange = (e) => {
        let value = Array.from(e.target.selectedOptions, option => option.value);
        setData({ ...data, [e.target.name]: value });
    };

    const isAdmin = usePage().props.auth?.role?.slug === 'admin';

    const handleLogoChange = (e) => {
        const file = e.target.files?.[0];

        if (file) {
            setData('hospital_logo', file);
        }
    };
    /**
  * Hospital type change
  */
    const handleTypeChange = (value: string) => {
        const hospitalTypeId = Number(value);

        setData('hospital_type_id', hospitalTypeId);
    };
    return (
        <AppLayout

        >
            <Head title="Create Hospital" />

            <div className="container mx-auto max-w-6xl px-4 py-8">
                {/* Page Header */}
                <div className="mb-6 flex items-center gap-3">
                    <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10">
                        <Hospital className="h-6 w-6 text-primary" />
                    </div>

                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Create Hospital
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Add a new hospital to the system.
                        </p>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2">
                                <Hospital className="h-5 w-5" />
                                Hospital Information
                            </CardTitle>

                            <CardDescription>
                                Enter the hospital details, contact information,
                                location and branding.
                            </CardDescription>
                        </CardHeader>

                        <CardContent className="space-y-8 pt-6">
                            {/* Basic Information */}
                            <section>
                                <div className="mb-4">
                                    <h2 className="text-base font-semibold">
                                        Basic Information
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Provide the hospital's basic details.
                                    </p>
                                </div>

                                <div className="grid gap-5 md:grid-cols-2">
                                    {/* Hospital Name */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="hospital_name">
                                            Hospital Name
                                            <span className="ml-1 text-destructive">
                                                *
                                            </span>
                                        </Label>

                                        <Input
                                            id="hospital_name"
                                            name="hospital_name"
                                            value={data.hospital_name}
                                            onChange={onHandleChange}
                                            placeholder="Enter hospital name"
                                            autoFocus
                                            required
                                        />

                                        {errors.hospital_name && (
                                            <p className="text-sm text-destructive">
                                                {errors.hospital_name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="hospital_type_id">Hospital Type</Label>

                                        <Select
                                            value={data.hospital_type_id?.toString() ?? ''}
                                            onValueChange={handleTypeChange}
                                        >
                                            <SelectTrigger id="hospital_type_id" className="w-full">
                                                <SelectValue
                                                    placeholder={
                                                        loadingHospitals
                                                            ? 'Loading hospital types...'
                                                            : 'Select Hospital Type'
                                                    }
                                                />
                                            </SelectTrigger>

                                            <SelectContent>
                                                {hospitalTypes.map((type) => (
                                                    <SelectItem
                                                        key={type.id}
                                                        value={type.id.toString()}
                                                    >
                                                        {type.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        {errors.hospital_type_id && (
                                            <p className="text-sm font-medium text-destructive">
                                                {errors.hospital_type_id}
                                            </p>
                                        )}
                                    </div>

                                    {/* Description */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="hospital_description">
                                            Description
                                        </Label>

                                        <Textarea
                                            id="hospital_description"
                                            name="hospital_description"
                                            value={data.hospital_description}
                                            onChange={onHandleChange}
                                            placeholder="Write a short description about the hospital..."
                                            className="min-h-[120px]"
                                        />

                                        {errors.hospital_description && (
                                            <p className="text-sm text-destructive">
                                                {errors.hospital_description}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </section>

                            {/* Contact Information */}
                            <section className="border-t pt-6">
                                <div className="mb-4">
                                    <h2 className="text-base font-semibold">
                                        Contact Information
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Add phone and website information.
                                    </p>
                                </div>

                                <div className="grid gap-5 md:grid-cols-2">
                                    {/* Mobile */}
                                    <div className="space-y-2">
                                        <Label htmlFor="mobile_number">
                                            Mobile Number
                                            <span className="ml-1 text-destructive">
                                                *
                                            </span>
                                        </Label>

                                        <Input
                                            id="mobile_number"
                                            name="mobile_number"
                                            value={data.mobile_number}
                                            onChange={onHandleChange}
                                            placeholder="01XXXXXXXXX"
                                            required
                                        />

                                        {errors.mobile_number && (
                                            <p className="text-sm text-destructive">
                                                {errors.mobile_number}
                                            </p>
                                        )}
                                    </div>

                                    {/* Phone */}
                                    <div className="space-y-2">
                                        <Label htmlFor="phone_number">
                                            Phone Number
                                        </Label>

                                        <Input
                                            id="phone_number"
                                            name="phone_number"
                                            value={data.phone_number}
                                            onChange={onHandleChange}
                                            placeholder="Enter phone number"
                                        />

                                        {errors.phone_number && (
                                            <p className="text-sm text-destructive">
                                                {errors.phone_number}
                                            </p>
                                        )}
                                    </div>

                                    {/* Website */}
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="hospital_url">
                                            Website URL
                                        </Label>

                                        <Input
                                            id="hospital_url"
                                            name="hospital_url"
                                            value={data.hospital_url}
                                            onChange={onHandleChange}
                                            placeholder="https://example.com"
                                        />

                                        {errors.hospital_url && (
                                            <p className="text-sm text-destructive">
                                                {errors.hospital_url}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </section>

                            {/* Address */}
                            <section className="border-t pt-6">
                                <div className="mb-4">
                                    <h2 className="text-base font-semibold">
                                        Location
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Enter the hospital address and geographic
                                        information.
                                    </p>
                                </div>

                                <div className="space-y-5">
                                    <div className="space-y-2">
                                        <Label htmlFor="address">
                                            Address
                                            <span className="ml-1 text-destructive">
                                                *
                                            </span>
                                        </Label>

                                        <Textarea
                                            id="address"
                                            name="address"
                                            value={data.address}
                                            onChange={onHandleChange}
                                            placeholder="Enter complete hospital address"
                                            className="min-h-[100px]"
                                            required
                                        />

                                        {errors.address && (
                                            <p className="text-sm text-destructive">
                                                {errors.address}
                                            </p>
                                        )}
                                    </div>

                                    <GeoLocation
                                        data={data}
                                        setData={setData}
                                        errors={errors}
                                    />
                                </div>
                            </section>

                            {/* Admin Settings */}
                            {isAdmin && (
                                <section className="border-t pt-6">
                                    <div className="mb-4">
                                        <h2 className="text-base font-semibold">
                                            Admin Settings
                                        </h2>
                                        <p className="text-sm text-muted-foreground">
                                            Configure display and visibility settings.
                                        </p>
                                    </div>

                                    <div className="grid gap-5 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="sort_order">
                                                Display Order
                                            </Label>

                                            <Input
                                                id="sort_order"
                                                type="number"
                                                name="sort_order"
                                                value={data.sort_order}
                                                onChange={onHandleChange}
                                                min="0"
                                            />

                                            {errors.sort_order && (
                                                <p className="text-sm text-destructive">
                                                    {errors.sort_order}
                                                </p>
                                            )}
                                        </div>

                                        <div className="flex items-center gap-3 pt-7">
                                            <Checkbox
                                                id="status"
                                                checked={data.status}
                                                onCheckedChange={(checked) =>
                                                    setData(
                                                        'status',
                                                        checked === true
                                                    )
                                                }
                                            />

                                            <div>
                                                <Label
                                                    htmlFor="status"
                                                    className="cursor-pointer"
                                                >
                                                    Active Hospital
                                                </Label>

                                                <p className="text-xs text-muted-foreground">
                                                    Hospital will be visible to users.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {errors.status && (
                                        <p className="mt-2 text-sm text-destructive">
                                            {errors.status}
                                        </p>
                                    )}
                                </section>
                            )}

                            {/* Hospital Logo */}
                            <section className="border-t pt-6">
                                <div className="mb-4">
                                    <h2 className="text-base font-semibold">
                                        Hospital Branding
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Upload your hospital logo and banner.
                                    </p>
                                </div>

                                <div className="grid gap-6 md:grid-cols-2">
                                    {/* Logo */}
                                    <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                                        <Label htmlFor="profile_image">Profile Image</Label>
                                        <LogoCropper data={data} setData={setData} field="hospital_logo" initialImage={hospital.data[0].logo_image_url} />
                                        {errors.hospital_logo && (
                                            <p className="mt-2 text-sm text-destructive">
                                                {errors.hospital_logo}
                                            </p>
                                        )}
                                    </div>

                                    {/* Banner */}
                                    <div className="space-y-3">
                                        <Label>
                                            Hospital Banner
                                        </Label>

                                        <div className="rounded-lg border p-4">
                                            <p className="mb-3 text-xs text-muted-foreground">
                                                Recommended aspect ratio: 16:9
                                            </p>

                                            <BannerCropper
                                                data={data}
                                                setData={setData}
                                                field="banner_url"
                                                errors={errors}
                                                initialImage={hospital.data[0].banner_url}
                                                fieldCropData="banner_crop_data"
                                            />
                                        </div>

                                        {errors.banner_url && (
                                            <p className="text-sm text-destructive">
                                                {errors.banner_url}
                                            </p>
                                        )}

                                        {errors.banner_crop_data && (
                                            <p className="text-sm text-destructive">
                                                {errors.banner_crop_data}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </section>
                        </CardContent>

                        {/* Footer Actions */}
                        <div className="flex flex-col-reverse gap-3 border-t bg-muted/30 px-6 py-4 sm:flex-row sm:justify-end">
                            {isAdmin && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        router.visit(
                                            route('hospital.index'),
                                            { method: 'get' }
                                        )
                                    }
                                    disabled={processing}
                                >
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Back
                                </Button>
                            )}

                            <Button
                                type="submit"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Saving...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-4 w-4" />
                                        Save Hospital
                                    </>
                                )}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}
