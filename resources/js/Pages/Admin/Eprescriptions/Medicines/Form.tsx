import MainForm from '@/components/Form/MainForm';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/react';
import { Select } from 'antd';

export default function Form({ data, setData, handleSubmit, errors, processing, manufacturers, heading, submitTitle = 'Create' }) {
    const handleCancel = () => {
        router.get(route('medicines.index'));
    };
    return (
        <MainForm handleSubmit={handleSubmit} handleCancel={handleCancel} processing={processing} submitTitle={submitTitle}>
            <h1 className="items-start !text-left font-extrabold">{heading}</h1>
            <div>
                <Label htmlFor="brand_name" className="block text-sm font-medium">
                    Brand Name
                </Label>
                <Input
                    id="brand_name"
                    type="text"
                    value={data.brand_name}
                    onChange={(e) => setData('brand_name', e.target.value)}
                    className="w-full rounded border px-3 py-2"
                    required={true}
                />
                {errors.brand_name && <div className="text-red-500">{errors.brand_name}</div>}
            </div>

            <div>
                <Label htmlFor="generic_name" className="block text-sm font-medium">
                    Generic Name
                </Label>
                <Input
                    id="generic_name"
                    type="text"
                    value={data.generic_name}
                    onChange={(e) => setData('generic_name', e.target.value)}
                    className="w-full rounded border px-3 py-2"
                />
                {errors.generic_name && <div className="text-red-500">{errors.generic_name}</div>}
            </div>

            <div>
                <Label htmlFor="strength" className="block text-sm font-medium">
                    Strength
                </Label>
                <Input
                    id="strength"
                    type="text"
                    value={data.strength}
                    onChange={(e) => setData('strength', e.target.value)}
                    className="w-full rounded border px-3 py-2"
                    required={true}
                />
                {errors.strength && <div className="text-red-500">{errors.strength}</div>}
            </div>

            <div>
                <Label htmlFor="type" className="block text-sm font-medium">
                    Type
                </Label>
                <Input
                    id="type"
                    type="text"
                    value={data.type}
                    onChange={(e) => setData('type', e.target.value)}
                    className="w-full rounded border px-3 py-2"
                    required={true}
                />
                {errors.type && <div className="text-red-500">{errors.type}</div>}
            </div>

            <div>
                <Label htmlFor="manufacturer_id" className="block text-sm font-medium">
                    Manufacturer
                </Label>
                <Select
                    id="manufacturer_id"
                    placeholder="Select Manufacturer"
                    allowClear
                    showSearch
                    optionFilterProp="label"
                    value={data.manufacturer_id || undefined}
                    onChange={(value) => setData('manufacturer_id', value)}
                    className="basic-single m-0 h-10 w-full"
                    options={manufacturers}
                    required={true}
                />
                {errors.manufacturer_id && <div className="text-red-500">{errors.manufacturer_id}</div>}
            </div>
        </MainForm>
    );
}
