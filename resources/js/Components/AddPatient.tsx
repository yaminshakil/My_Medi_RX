import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select } from 'antd';
import React from 'react';

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

export default function AddPatient({ patientData, setPatientData, onHandleChange, errorsPateint }) {
    const maritalstatusoptions = [
        { label: 'Single', value: 'single' },
        { label: 'Married', value: 'married' },
        { label: 'Divorced', value: 'divorced' },
        { label: 'Widowed', value: 'widowed' },
    ];

    return (
        <React.Fragment>
            <form className="mx-auto w-full space-y-4 pb-4">
                <div className="mt-0">
                    <Label className="block after:text-red-500 after:content-['*']">Name</Label>
                    <Input
                        type="text"
                        name="name"
                        placeholder="Name"
                        value={patientData.name}
                        onChange={onHandleChange}
                        className="w-full border p-2"
                        required={true}
                    />
                    {patientData.name == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.name} />}
                </div>
                <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div className="min-w-0">
                        <Label className="block after:text-red-500 after:content-['*']">Phone</Label>
                        <Input
                            type="text"
                            name="phone"
                            placeholder="Phone"
                            value={patientData.phone}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                            required={true}
                        />
                        {patientData.phone == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.phone} />}
                    </div>

                    <div className="min-w-0">
                        <Label className="block after:text-red-500 after:content-['*']">Email</Label>
                        <Input
                            type="text"
                            name="email"
                            placeholder="Email"
                            value={patientData.email}
                            onChange={onHandleChange}
                            className="w-full border p-2"
                            required={true}
                        />
                        {patientData.email == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.email} />}
                    </div>
                </div>
                <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div className="min-w-0">
                        <Label htmlFor="gender" className="block after:text-red-500 after:content-['*']">
                            Gender
                        </Label>
                        <Select
                            data-testid="select-gender"
                            placeholder="Select Gender"
                            showSearch
                            optionFilterProp="label"
                            id="gender"
                            value={patientData.gender || undefined}
                            onChange={(value) => setPatientData('gender', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={options}
                            required={true}
                        />
                        {patientData.gender == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.gender} />}
                    </div>

                    <div className="min-w-0">
                        <Label htmlFor="blood_group" className="block">
                            Blood Group
                        </Label>
                        <Select
                            data-testid="blood-group-select"
                            placeholder="Select Blood Group"
                            optionFilterProp="label"
                            id="blood_group"
                            value={patientData.blood_group || undefined}
                            onChange={(value) => setPatientData('blood_group', value)}
                            className="basic-single m-0 h-10 w-full"
                            options={bloodgroup}
                        />
                        {patientData.blood_group == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.blood_group} />}
                    </div>
                </div>

                <div>
                    <Label className="block text-sm font-medium text-gray-700">Marital status</Label>
                    <Select
                        placeholder="Select Marital status"
                        optionFilterProp="label"
                        value={patientData.marital_status || undefined}
                        onChange={(value) => setPatientData('marital_status', value)}
                        className="basic-single m-0 h-10 w-full"
                        options={maritalstatusoptions}
                    />
                    <InputError className="mt-2 text-red-700" message={errorsPateint.marital_status} />
                </div>

                <div className="mt-4">
                    <Label className="block">Address</Label>
                    <Textarea
                        name="address"
                        className="w-full border p-2"
                        placeholder="Enter address"
                        value={patientData.address}
                        onChange={(e) => setPatientData('address', e.target.value)}
                    ></Textarea>
                </div>
                <div className="mt-4">
                    <Label className="block">City</Label>
                    <Input
                        type="text"
                        name="city"
                        className="w-full border p-2"
                        placeholder="Enter city"
                        value={patientData.city}
                        onChange={onHandleChange}
                    />
                </div>

                <div className="w-fit-content mt-4">
                    <Label htmlFor="dob" className="after:text-red-500 after:content-['*']">
                        Date of Birth
                    </Label>
                    <Input
                        type="date"
                        name="date_of_birth"
                        className="w-full border p-2"
                        placeholder="Date of Birth"
                        value={patientData.date_of_birth}
                        onChange={onHandleChange}
                    />
                    {patientData.date_of_birth == '' && <InputError className="mt-2 text-red-700" message={errorsPateint.date_of_birth} />}
                </div>
            </form>
        </React.Fragment>
    );
}
