import MainForm from '@/Components/Form/MainForm';
import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { router } from '@inertiajs/react';

interface FormProps {
    data: unknown;
    setData: (key: string, value: unknown) => void;
    errors: Record<string, string>;
    handleSubmit: (e: React.FormEvent) => void;
    processing: boolean;
    submitTitle: string;
    heading: string;
}

export default function Form({ data, setData, errors, handleSubmit, processing, submitTitle, heading }: FormProps) {
    const handleCancel = () => {
        router.get(route('permissions.index'));
    };

    return (
        <MainForm handleSubmit={handleSubmit} handleCancel={handleCancel} processing={processing} submitTitle={submitTitle}>
            <h1 className="items-start !text-left font-extrabold">{heading}</h1>
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autoFocus
                    tabIndex={1}
                    autoComplete="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    disabled={processing}
                    placeholder="Name"
                />
                <InputError message={errors.name} className="mt-2" />
            </div>
        </MainForm>
    );
}
