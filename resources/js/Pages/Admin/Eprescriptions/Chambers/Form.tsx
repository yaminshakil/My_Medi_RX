import FormFooter from '@/Components/Form/FormFooter';
import InputError from '@/Components/input-error';
import LogoCropper from '@/Components/LogoCropper';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { router } from '@inertiajs/react';
import { Editor } from 'primereact/editor';
import { FormEventHandler, useState } from 'react';
import ScheduleRow from './ScheduleRow';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

export default function Form({ data, setData, errors, handleSubmit, processing, heading, submitTitle, initialImage, hospitals }) {
    // handle change for a specific row
    const [loadingHospitals, setLoadingHospitals] = useState(false);
    const handleChange = (index, field, value) => {
        const updated = [...data.schedules];
        updated[index][field] = value;
        setData('schedules', updated);
    };

    const addRow = () => {
        setData('schedules', [...data.schedules, { day: '', start_time: '', end_time: '', slot_duration: '' }]);
    };

    const removeRow = (index) => {
        const updated = data.schedules.filter((_, i) => i !== index);
        setData('schedules', updated.length ? updated : [{ day: '', start_time: '', end_time: '', slot_duration: '' }]);
    };

    const onHandleChange: FormEventHandler = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleCancel = () => {
        router.visit(route('chambers.index'));
    };

    /**
   * Hospital type change
   */
    const handleHospitalChange = (value: string) => {
        const hospitalId = Number(value);

        setData('hospital_id', hospitalId);
    };

    return (
        <form role="form" className="w-full border p-4 shadow-md sm:rounded-lg" onSubmit={handleSubmit}>
            <div className="grid w-full grid-cols-1 gap-6 md:grid-cols-2">
                <h1 className="col-span-1 items-start !text-left font-extrabold md:col-span-3"> {heading}</h1>
                <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                    <h2 className="col-span-3 mb-2 text-left text-xl font-extrabold">Chamber Info</h2>
                    <div className="w-[300px]">
                        <Label>Chamber Logo</Label>
                        <LogoCropper data={data} setData={setData} field="chamber_logo" initialImage={initialImage} />
                        <InputError message={errors.chamber_logo} className="mt-2 text-red-700" />
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="name" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Name
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={onHandleChange}
                            required
                            autoComplete="name"
                            tabIndex={1}
                            placeholder="Name"
                            disabled={processing}
                        />
                        {data.name == '' && errors.name && <InputError className="mt-2 text-red-700" message="Name field is required" />}
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="hospital_type_id">Hospital</Label>

                        <Select
                            value={data.hospital_id?.toString() ?? ''}
                            onValueChange={handleHospitalChange}
                        >
                            <SelectTrigger id="hospital_id" className="w-full">
                                <SelectValue
                                    placeholder={
                                        loadingHospitals
                                            ? 'Loading hospital ...'
                                            : 'Select Hospital'
                                    }
                                />
                            </SelectTrigger>

                            <SelectContent>
                                {hospitals.map((hospital) => (
                                    <SelectItem
                                        key={hospital.value}
                                        value={hospital.value.toString()}
                                    >
                                        {hospital.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {errors.hospital_id && (
                            <p className="text-sm font-medium text-destructive">
                                {errors.hospital_id}
                            </p>
                        )}
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="city" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            City
                        </Label>
                        <div className="flex w-full items-center gap-2">
                            <div className="flex-1">
                                <Input
                                    id="city"
                                    name="city"
                                    className="mt-1 block w-full"
                                    value={data.city}
                                    onChange={onHandleChange}
                                    required
                                    autoComplete="city"
                                    tabIndex={2}
                                    placeholder="City"
                                    disabled={processing}
                                />
                            </div>
                        </div>
                        {data.city == '' && errors.city && <InputError className="mt-2 text-red-700" message={errors.city} />}
                    </div>

                    <div className="mt-4 w-full">
                        <Label htmlFor="address" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Address
                        </Label>
                        <div className="flex w-full items-center gap-2">
                            <div className="flex-1">
                                <Textarea
                                    id="address"
                                    value={data.address || ''}
                                    onChange={(e) => setData('address', e.target.value)}
                                    disabled={processing}
                                    placeholder="Address"
                                    tabIndex={3}
                                />
                            </div>
                        </div>
                        {data.city == '' && errors.city && <InputError className="mt-2 text-red-700" message={errors.city} />}
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="fee" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Fee
                        </Label>
                        <Input
                            id="fee"
                            name="fee"
                            data-testid="fee-input"
                            className="mt-1 block w-full"
                            value={data.fee}
                            onChange={onHandleChange}
                            required
                            autoComplete="fee"
                            tabIndex={4}
                            disabled={processing}
                        />
                        <InputError message={errors.fee} className="mt-2 text-red-700" />
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="followup_fee" className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Followup Fee
                        </Label>
                        <Input
                            id="followup_fee"
                            name="followup_fee"
                            className="mt-1 block w-full"
                            value={data.followup_fee}
                            onChange={onHandleChange}
                            required
                            autoComplete="followup_fee"
                            tabIndex={5}
                            disabled={processing}
                        />
                        <InputError message={errors.followup_fee} className="mt-2 text-red-700" />
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="report_fee" className="block text-sm font-medium text-gray-700">
                            Report Fee
                        </Label>
                        <Input
                            id="report_fee"
                            name="report_fee"
                            className="mt-1 block w-full"
                            value={data.report_fee}
                            onChange={onHandleChange}
                            autoComplete="report_fee"
                            tabIndex={6}
                            disabled={processing}
                        />
                    </div>
                    <div className="mt-4 w-full">
                        <Label htmlFor="appoinment_limit" className="block text-sm font-medium text-gray-700">
                            Appoinment Limit
                        </Label>
                        <Input
                            type="number"
                            min={0}
                            id="appoinment_limit"
                            name="appoinment_limit"
                            className="mt-1 block w-full"
                            value={data.appoinment_limit}
                            onChange={onHandleChange}
                            autoComplete="appoinment_limit"
                            tabIndex={6}
                            disabled={processing}
                        />
                    </div>
                </div>
                <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                    <h2 className="col-span-3 mb-2 text-left text-xl font-extrabold">Prescription Info</h2>
                    <div className="card mt-4">
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Presctiption Header Left
                        </Label>
                        <Editor
                            value={data.header_left}
                            onTextChange={(e) => {
                                setData('header_left', e.htmlValue);
                            }}
                            style={{ height: '220px' }}
                        />
                        {(data.header_left == '' || data.header_left == null) && (
                            <InputError className="mt-2 text-red-700" message={errors.header_left} />
                        )}
                    </div>
                    <div className="card mt-4">
                        <Label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">
                            Presctiption Header Right
                        </Label>
                        <Editor
                            value={data.header_right}
                            onTextChange={(e) => {
                                setData('header_right', e.htmlValue);
                            }}
                            style={{ height: '220px' }}
                        />
                        {(data.header_right == '' || data.header_right == null) && (
                            <InputError className="mt-2 text-red-700" message={errors.header_right} />
                        )}
                    </div>
                    <div className="card mt-4">
                        <label className="block text-sm font-medium text-gray-700 after:text-red-500 after:content-['*']">Presctiption Footer</label>
                        <Editor value={data.footer_info} onTextChange={(e) => setData('footer_info', e.htmlValue)} style={{ height: '220px' }} />
                        {(data.footer_info == '' || data.footer_info == null) && (
                            <InputError className="mt-2 text-red-700" message={errors.footer_info} />
                        )}
                    </div>
                </div>
            </div>
            <div className="mt-4 mb-2">
                <h2 className="text-xl font-bold">Set Weekly Schedule</h2>
                <div className="space-y-4">
                    {data.schedules.map((row, index) => (
                        <ScheduleRow key={index} index={index} row={row} errors={errors} handleChange={handleChange} removeRow={removeRow} />
                    ))}
                </div>
                {/* Add Row */}
                <Button type="button" onClick={addRow} className="mt-4 border px-4 py-2 text-white">
                    + Add More
                </Button>
            </div>
            <FormFooter handleCancel={handleCancel} processing={processing} submitTitle={submitTitle} />
        </form>
    );
}
