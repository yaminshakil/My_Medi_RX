import InputError from '@/Components/input-error';
import LogoCropper from '@/Components/LogoCropper';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select } from 'antd';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';

export default function Form({ data, setData, errors, handleSubmit, processing, initialImage, setShowConfirm, is_delete, submitTitle }) {
    const bloodgroup = [
        { value: 'A+', label: 'A+' },
        { value: 'A-', label: 'A-' },
        { value: 'B+', label: 'B+' },
        { value: 'B-', label: 'B-' },
        { value: 'AB+', label: 'AB+' },
        { value: 'AB-', label: 'AB-' },
        { value: 'O+', label: 'O+' },
        { value: 'O-', label: 'O-' },
    ];

    const options = [
        { label: 'Male', value: 'male' },
        { label: 'Female', value: 'female' },
        { label: 'Other', value: 'other' },
    ];
    const maritalstatusoptions = [
        { label: 'Single', value: 'single' },
        { label: 'Married', value: 'married' },
        { label: 'Divorced', value: 'divorced' },
        { label: 'Widowed', value: 'widowed' },
    ];

    const onHandleChange: FormEventHandler = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const calculateAge = (dob: string): number => {
    if (!dob) return 0;

    const birthDate = new Date(dob);
    const today = new Date();

    if (birthDate > today) return 0;

    let age = today.getFullYear() - birthDate.getFullYear();

    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birthDate.getDate())
    ) {
        age--;
    }

    return age;
};

useEffect(() => {
    if (data.date_of_birth) {
        const calculatedAge = calculateAge(data.date_of_birth);

        // Update only if age is different
        if (Number(data.age) !== calculatedAge) {
            setData('age', calculatedAge);
        }
    }
}, [data.date_of_birth]);

    return (
        <form role="form" onSubmit={handleSubmit} className="flex w-full flex-col gap-4 p-4 shadow-md sm:rounded-lg">
            <div className="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
                <h1 className="col-span-1 mb-4 text-left text-xl font-extrabold md:col-span-3">Patient Profile</h1>
                <InputError message={errors.user_id} className="col-span-1 mt-2 text-xl font-extrabold md:col-span-3" />
                {/* Left Column - Profile Image */}
                <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                    <Label htmlFor="profile_image">Profile Image</Label>
                    <LogoCropper data={data} setData={setData} field="profile_image" initialImage={initialImage} />
                    <InputError message={errors.profile_image} className="mt-2" />
                </div>
                {/* Middle Column - Basic Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div>
                        <Label htmlFor="name" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Name
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            placeholder="Name"
                            value={data.name}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                            required={true}
                            disabled={processing}
                        />
                        {data.name == '' && <InputError className="mt-2 text-red-700" message={errors.name} />}
                    </div>
                    <div>
                        <Label htmlFor="phone" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Phone
                        </Label>
                        <Input
                            id="phone"
                            type="text"
                            name="phone"
                            placeholder="Phone"
                            value={data.phone}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                            required={true}
                            disabled={processing}
                        />
                        {data.phone == '' && <InputError className="mt-2 text-red-700" message={errors.phone} />}
                    </div>

                    <div>
                        <Label htmlFor="email" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Email
                        </Label>
                        <Input
                            id="email"
                            type="text"
                            name="email"
                            placeholder="Email"
                            value={data.email}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                            required={true}
                            disabled={processing}
                        />
                        {data.email == '' && <InputError className="mt-2 text-red-700" message={errors.email} />}
                    </div>

                    <div>
                        <Label htmlFor="gender" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Gender
                        </Label>
                        <Select
                            id="gender"
                            placeholder="Select Gender"
                            showSearch
                            optionFilterProp="label"
                            name="gender"
                            value={data.gender || undefined}
                            onChange={(value) => setData('gender', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={options}
                            required={true}
                            disabled={processing}
                        />
                        {data.gender == '' && <InputError className="mt-2 text-red-700" message={errors.gender} />}
                    </div>

                    <div>
                        <Label htmlFor="blood_group" className="block text-sm font-medium text-gray-700">
                            Blood Group
                        </Label>
                        <Select
                            id="blood_group"
                            placeholder="Select Blood Group"
                            optionFilterProp="label"
                            name="blood_group"
                            value={data.blood_group || undefined}
                            onChange={(value) => setData('blood_group', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={bloodgroup}
                            disabled={processing}
                        />
                        {data.blood_group == '' && <InputError className="mt-2 text-red-700" message={errors.blood_group} />}
                    </div>
                </div>

                {/* Right Column - Professional Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div>
                        <Label htmlFor="marital_status" className="block text-sm font-medium text-gray-700">
                            Marital status
                        </Label>
                        <Select
                            id="marital_status"
                            placeholder="Select Marital status"
                            optionFilterProp="label"
                            disabled={processing}
                            value={data.marital_status || undefined}
                            onChange={(value) => setData('marital_status', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={maritalstatusoptions}
                        />
                        <InputError className="mt-2 text-red-700" message={errors.marital_status} />
                    </div>

                    <div>
                        <Label htmlFor="address" className="block text-sm font-medium text-gray-700">
                            Address
                        </Label>
                        <Textarea
                            id="address"
                            name="address"
                            className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter address"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            disabled={processing}
                        ></Textarea>
                        <InputError className="mt-2 text-red-700" message={errors.address} />
                    </div>
                    <div>
                        <Label htmlFor="city" className="block text-sm font-medium text-gray-700">
                            City
                        </Label>
                        <Input
                            id="city"
                            type="text"
                            name="city"
                            className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Enter city"
                            value={data.city}
                            onChange={onHandleChange}
                            disabled={processing}
                        />
                        <InputError className="mt-2 text-red-700" message={errors.city} />
                    </div>

                    <div>
                        <Label htmlFor="date_of_birth" className="after:text-red-500 after:content-['*']">
                            Date of Birth
                        </Label>
                        <Input
                            data-testid="date_of_birth"
                            id="date_of_birth"
                            type="date"
                            required={true}
                            onChange={(e) => {
                                const dob = e.target.value;
                                setData((prev) => ({
                                    ...prev,
                                    date_of_birth: dob,
                                    age: calculateAge(dob),
                                }));
                            }}
                            value={data.date_of_birth}
                            name="date_of_birth"
                            disabled={processing}
                            className="w-full bg-white sm:w-auto"
                        />

                        <InputError className="mt-2 text-red-700" message={errors.date_of_birth} />
                    </div>
                    <div>
                        <Label
                            htmlFor="age"
                            className="after:text-red-500 after:content-['*']"
                        >
                            Age
                        </Label>
                        <Input
                            id="age"
                            type="text"
                            required={true}
                            onChange={(e) => {
                                setData("age", parseInt(e.target.value) || 0);
                            }}
                            value={data.age}
                            name="age"
                            className="w-full bg-white sm:w-auto"
                            disabled={processing}
                        />

                        <InputError
                            className="mt-2 text-red-700"
                            message={errors.age}
                        />
                    </div>
                </div>
            </div>

            <div className="mt-4 flex justify-start gap-2">
                <Button type="submit" className="mt-2 w-fit" disabled={processing}>
                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                    {submitTitle}
                </Button>
                {is_delete && (
                    <Button
                        variant="destructive"
                        type="button"
                        onClick={() => {
                            setShowConfirm(true);
                        }}
                        className="mt-2 w-fit"
                    >
                        Delete
                    </Button>
                )}
            </div>
        </form>
    );
}
