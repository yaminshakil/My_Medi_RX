import React, { useState, useEffect } from "react";
import Authenticated from '@/Layouts/Authenticated';
import Button from '@/Components/Button';
import Input from '@/Components/Input';
import InputError from '@/Components/InputError';
import Label from '@/Components/Label';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import Checkbox from '@/Components/Checkbox';
import TextArea from '@/Components/TextArea';
import GeoLocation from '@/Components/GeoLocation';
import BannerCropper from '@/Components/BannerCropper';

export default function Edit(props) {
    const { hospital } = usePage().props;
    console.log(hospital.data[0].banner_url);
    const { data, setData, post, processing, errors, reset } = useForm({
        hospital_name: hospital.data[0].hospital_name || '',
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



    return (
        <Authenticated
            auth={props.auth}
            errors={props.errors}
            menu={props.menu}
            dashboardlogoUrl={props.dashboardlogoUrl}
        >
            <div className="container mx-4 my-12">
                <Head title="Edit Hospital" />
                <div className="flex items-center justify-end mt-4">
                    <button
                        type='button'
                        className={
                            `ml-4 bg-lime-700 inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-gray-900 transition ease-in-out duration-150`
                        }
                        onClick={() => { router.visit(route('hospital.index'), { method: 'get' }) }}
                    >
                        Back
                    </button>
                </div>

                <form className="w-full sm:w-2/3 mx-auto pb-4" onSubmit={submit}>
                    <fieldset className="border border-solid border-gray-300 p-3 mt-4">
                        <legend>Edit Hospital</legend>
                        <div>
                            <Label forInput="hospital_name" value="Hospital Name" />
                            <Input
                                type="text"
                                name="hospital_name"
                                value={data.hospital_name}
                                className="mt-1 block w-full"
                                autoComplete="hospital_name"
                                isFocused={true}
                                handleChange={onHandleChange}
                                required
                            />
                            <InputError message={errors.hospital_name} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label forInput="hospital_description" value="Hospital Description *" />
                            <TextArea
                                name="hospital_description"
                                value={data.hospital_description}
                                className="mt-1 block w-full h-36"
                                autoComplete="hospital_description"
                                handleChange={onHandleChange}

                            />

                            <InputError message={errors.hospital_description} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label forInput="mobile_number" value="Mobile Number" />
                            <Input
                                type="text"
                                name="mobile_number"
                                value={data.mobile_number}
                                className="mt-1 block w-full"
                                autoComplete="mobile_number"
                                isFocused={true}
                                handleChange={onHandleChange}
                                required
                            />
                            <InputError message={errors.mobile_number} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label forInput="phone_number" value="Phone Number" />
                            <Input
                                type="text"
                                name="phone_number"
                                value={data.phone_number}
                                className="mt-1 block w-full"
                                autoComplete="phone_number"
                                isFocused={true}
                                handleChange={onHandleChange}

                            />
                            <InputError message={errors.phone_number} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label forInput="hospital_url" value="Hospital Url" />
                            <Input
                                type="text"
                                name="hospital_url"
                                value={data.hospital_url}
                                className="mt-1 block w-full"
                                autoComplete="hospital_url"
                                isFocused={true}
                                handleChange={onHandleChange}

                            />
                            <InputError message={errors.hospital_url} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <Label forInput="address" value="Hospital Address" />
                            <TextArea
                                name="address"
                                value={data.address}
                                className="mt-1 block w-full"
                                autoComplete="address"
                                handleChange={onHandleChange}
                                required={true}
                            />

                            <InputError message={errors.address} className="mt-2" />
                        </div>

                        <GeoLocation data={data} setData={setData} errors={errors} />

                        <div className="mt-4 flex justify-start items-center w-full">
                            <Label className="w-28 text-left mr-1" forInput="sort_order" value="Display Order" />
                            <Input
                                type="number"
                                name="sort_order"
                                value={data.sort_order}
                                className="mt-1 block w-20"
                                autoComplete="sort_order"
                                isFocused={true}
                                handleChange={onHandleChange}
                            />
                            <InputError message={errors.sort_order} className="mt-2" />
                        </div>

                        <div className="mt-4 w-full">
                            <Label forInput="hospital_logo" value="Hospital Logo (Dim 800x800, png, jpg)" />

                            <input className="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 py-[0.32rem] text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:-my-[0.32rem] file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:py-[0.32rem] file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[border-inline-end-width:1px] file:[margin-inline-end:0.75rem] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-te-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100 dark:focus:border-primary" accept="image/*" type="file" name="hospital_logo" value={''} onChange={e => { setData('hospital_logo', e.target?.files[0]); }} />
                            {data.hospital_logo instanceof File ? <img className="mt-1" width="200" src={URL.createObjectURL(data.hospital_logo)} /> : data.hospital_logo ? <img className="mt-1" width="200" src={usePage().props?.imageUrl + '/storage/' + data.hospital_logo} /> : null}
                            <InputError message={errors.hostpital_log} className="mt-2" />
                        </div>
                        {/* Banner Cropping Section */}
                        <div className="mt-4 w-full">
                            <Label forInput="banner_url" value="Hospital Banner (16:9 Aspect Ratio)" />
                            <BannerCropper
                                data={data}
                                setData={setData}
                                initialImage={hospital.data[0].banner_url ? `/storage/${hospital.data[0].banner_url}` : null}
                                initialCropData={hospital.data[0].banner_crop_data}
                            />
                            <InputError message={errors.banner_url} className="mt-2" />
                            <InputError message={errors.banner_crop_data} className="mt-2" />
                        </div>
                        <div className="grid grid-cols-2 gap-4 mt-4">
                            <div className="col-span-1 mt-4">
                                <label className="flex items-center">
                                    <Checkbox name="status" value={data.status} handleChange={onHandleChange} />

                                    <span className="ml-2 text-sm text-gray-600">Is Active</span>
                                </label>
                                <InputError message={errors.status} className="mt-2" />
                            </div>
                        </div>

                        <div className="flex items-center justify-end mt-4">
                            <Button className="ml-4 bg-lime-700" processing={processing}>
                                Save
                            </Button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </Authenticated>
    );
}