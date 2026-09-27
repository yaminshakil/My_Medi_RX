import MainForm from '@/Components/Form/MainForm';
import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { router } from '@inertiajs/react';

interface Role {
    id: number;
    name: string;
}

interface FormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    roles: Role[]; // array of role names
}

interface FormProps {
    data: FormData;
    setData: (key: string, value: unknown) => void;
    errors: Record<string, string>;
    handleSubmit: (e: React.FormEvent) => void;
    roles: Role[];
    processing: boolean;
    submitTitle: string;
    heading: string;
    isUpdate: boolean;
}

export default function Form({ data, setData, errors, handleSubmit, processing, roles, submitTitle, heading, isUpdate = false }: FormProps) {
    const handelCheckBoxChange = (roleName: string, checked: boolean) => {
        if (checked) {
            setData('roles', [...data.roles, roleName]);
        } else {
            setData(
                'roles',
                data.roles.filter((name) => name !== roleName),
            );
        }
    };

    const handleCancel = () => {
        router.visit(route('users.index'));
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
                <Label htmlFor="email">Email</Label>
                <Input
                    id="email"
                    type="text"
                    required
                    autoFocus
                    tabIndex={1}
                    autoComplete="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    disabled={processing}
                    placeholder="email"
                />
                <InputError message={errors.email} className="mt-2" />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="password" className={!isUpdate && "after:text-red-500 after:content-['*']"}>
                    Password
                </Label>
                <Input
                    id="password"
                    type="password"
                    required={!isUpdate}
                    autoFocus
                    tabIndex={1}
                    autoComplete="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    disabled={processing}
                    placeholder="password"
                />
                <InputError message={errors.password} className="mt-2" />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="password_confirmation" className={!isUpdate && "after:text-red-500 after:content-['*']"}>
                    Confirm password
                </Label>
                <Input
                    id="password_confirmation"
                    type="password"
                    required={!isUpdate}
                    tabIndex={4}
                    autoComplete="new-password"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    disabled={processing}
                    placeholder="Confirm password"
                />
                <InputError message={errors.password_confirmation} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="roles">Roles</Label>
                {roles.map((role) => (
                    <Label key={role.id} htmlFor={role.name} className="flex items-center space-x-2">
                        <Input
                            id={role.name}
                            type="checkbox"
                            checked={(data.roles || []).includes(role.name)}
                            value={role}
                            onChange={(e) => handelCheckBoxChange(role.name, e.target.checked)}
                            className="form-checkbox h-5 w-5 text-blue-600 focus:ring-2"
                        />
                        <span className="text-gray-800 capitalize">{role.name}</span>
                    </Label>
                ))}
                <InputError message={errors.roles} className="mt-2" />
            </div>
        </MainForm>
    );
}
