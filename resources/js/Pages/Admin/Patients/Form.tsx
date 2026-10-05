import FormFooter from '@/Components/Form/FormFooter';
import InputError from '@/Components/input-error';
import LogoCropper from '@/Components/LogoCropper';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { router } from '@inertiajs/react';
import { Select } from 'antd';

export default function Form({ data, setData, handleSubmit, processing, errors, submitTitle, initialImage }) {
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

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleCancel = () => {
        router.visit(route('patients.index'));
    };

    return (
        <form role="form" onSubmit={handleSubmit} className="flex w-full flex-col gap-4 border p-4 shadow-md sm:rounded-lg">
            <div className="grid w-full grid-cols-1 gap-6 md:grid-cols-3">
                <h1 className="col-span-1 text-left text-xl font-extrabold md:col-span-3">Profile</h1>
                {/* Left Column - Profile Image */}
                <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                    <Label htmlFor="profile_image">Profile Image</Label>
                    <LogoCropper data={data} setData={setData} field="profile_image" initialImage={initialImage} />
                    <InputError message={errors.profile_image} className="mt-2" />
                </div>
                {/* Middle Column - Basic Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div>
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">Name</Label>
                        <Input
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
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">Phone</Label>
                        <Input
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
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">Email</Label>
                        <Input
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
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">Gender</Label>
                        <Select
                            placeholder="Select Gender"
                            showSearch
                            optionFilterProp="label"
                            disabled={processing}
                            value={data.gender || undefined}
                            onChange={(value) => setData('gender', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={options}
                        />
                        {data.gender == '' && <InputError className="mt-2 text-red-700" message={errors.gender} />}
                    </div>

                    <div>
                        <Label className="block text-sm font-medium text-gray-700">Blood Group</Label>
                        <Select
                            placeholder="Select Blood Group"
                            optionFilterProp="label"
                            disabled={processing}
                            value={data.blood_group || undefined}
                            onChange={(value) => setData('blood_group', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={bloodgroup}
                        />
                        {data.blood_group == '' && <InputError className="mt-2 text-red-700" message={errors.blood_group} />}
                    </div>
                </div>

                {/* Right Column - Professional Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div>
                        <Label className="block text-sm font-medium text-gray-700">Marital status</Label>
                        <Select
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
                        <Label className="block text-sm font-medium text-gray-700">Address</Label>
                        <Textarea
                            name="address"
                            className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Address"
                            rows="3"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                        ></Textarea>
                        <InputError className="mt-2 text-red-700" message={errors.address} />
                    </div>
                    <div>
                        <Label className="block text-sm font-medium text-gray-700">City</Label>
                        <Input
                            type="text"
                            name="city"
                            className="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="City"
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
                            id="date_of_birth"
                            type="date"
                            required={true}
                            onChange={(e) => {
                                setData('date_of_birth', e.target.value);
                            }}
                            value={data.date_of_birth}
                            name="date_of_birth"
                            className="w-full bg-white sm:w-auto"
                            disabled={processing}
                        />

                        <InputError className="mt-2 text-red-700" message={errors.date_of_birth} />
                    </div>
                </div>
            </div>

            <FormFooter handleCancel={handleCancel} processing={processing} submitTitle={submitTitle} />
        </form>
    );
}
