import MainForm from '@/components/Form/MainForm';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { Select } from 'antd';

const { Option } = Select;

interface Parents {
    id: number;
    name: string;
}

interface FormData {
    name: string;
    icon: string;
    description: string;
    is_surgical: boolean;
    role: string;
    parent_id: number;
}

interface FormProps {
    data: FormData;
    setData: (key: string, value: unknown) => void;
    errors: Record<string, string>;
    handleSubmit: (e: React.FormEvent) => void;
    processing: boolean;
    submitTitle: string;
    heading: string;
    parents: Parents[];
}

export default function Form({ parents, data, setData, handleSubmit, processing, errors, submitTitle = 'Create', heading }: FormProps) {
    const handleCancel = () => {
        router.get(route('medical-specialties.index'));
    };

    return (
        <MainForm handleSubmit={handleSubmit} handleCancel={handleCancel} processing={processing} submitTitle={submitTitle}>
            <h1 className="items-start !text-left font-extrabold">{heading}</h1>

            {/* Name */}
            <div className="grid gap-2">
                <Label htmlFor="name" className="after:text-red-500 after:content-['*']">
                    Name
                </Label>
                <Input id="name" className="w-full border" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                {errors.name && <InputError message={errors.name} />}
            </div>

            {/* Icon */}
            <div className="grid gap-2">
                <Label htmlFor="icon">Icon</Label>
                <Input id="icon" className="w-full border" value={data.icon} onChange={(e) => setData('icon', e.target.value)} />
            </div>

            {/* Description */}
            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    className="w-full border"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
            </div>

            {/* Parent Select with AntD */}
            <div className="grid gap-2">
                <Label htmlFor="parent_id">Parent</Label>
                <Select
                    id="parent_id"
                    allowClear
                    className="w-full"
                    value={data.parent_id ?? undefined}
                    onChange={(value) => setData('parent_id', value)}
                    placeholder="Select parent specialty"
                >
                    {Object.entries(parents).map(([id, name]) => (
                        <Option key={id} value={Number(id)}>
                            {name}
                        </Option>
                    ))}
                </Select>
            </div>

            {/* Surgical Checkbox */}
            <div className="flex items-center gap-2">
                <Input
                    id="is_surgical"
                    className="w-6"
                    type="checkbox"
                    checked={data.is_surgical}
                    onChange={(e) => setData('is_surgical', e.target.checked)}
                />
                <Label htmlFor="is_surgical">Surgical?</Label>
            </div>
        </MainForm>
    );
}
