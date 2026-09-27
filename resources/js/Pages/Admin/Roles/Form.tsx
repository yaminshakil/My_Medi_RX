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
    permissions: [];
    processing: boolean;
    submitTitle: string;
    heading: string;
}

export default function Form({ data, setData, errors, handleSubmit, permissions, processing, submitTitle, heading }: FormProps) {
    const handelCheckBoxChange = (permissionName, checked) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionName]);
        } else {
            setData(
                'permissions',
                data.permissions.filter((name) => name != permissionName),
            );
        }
    };

    const handleCancel = () => {
        router.visit(route('roles.index'));
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

            <div className="grid gap-2">
                <Label htmlFor="permissions">Parmisstions</Label>
                {permissions.map((permission) => (
                    <Label key={permission} className="flex items-center space-x-2">
                        <Input
                            id="permissions"
                            type="checkbox"
                            checked={data.permissions.includes(permission)}
                            value={permission}
                            onChange={(e) => handelCheckBoxChange(permission, e.target.checked)}
                            className="form-checkbox h-5 w-5 text-blue-600 focus:ring-2"
                        />
                        <span className="text-gray-800 capitalize">{permission}</span>
                    </Label>
                ))}
                <InputError message={errors.permissions} className="mt-2" />
            </div>
        </MainForm>
    );
}
