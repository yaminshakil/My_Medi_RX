import MainForm from '@/Components/Form/MainForm';
import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { router } from '@inertiajs/react';
import { Select } from 'antd';

interface Role {
    value: number;
    label: string;
}

interface Parentmenus {
    value: number;
    label: string;
}

interface FormData {
    name: string;
    slug: string;
    menu_method: string;
    menu_icon: string;
    role: string;
    parent_id: number;
    order_by: number;
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
    parentmenus: Parentmenus[];
}
export default function Form({ data, setData, handleSubmit, processing, errors, submitTitle = 'Create', roles, parentmenus, heading }: FormProps) {
    const onHandleChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleCancel = () => {
        router.get(route('menus.index'));
    };

    return (
        <MainForm handleSubmit={handleSubmit} handleCancel={handleCancel} processing={processing} submitTitle={submitTitle}>
            <h1 className="items-start !text-left font-extrabold">{heading}</h1>
            <div className="grid gap-2">
                <Label htmlFor="name" className="after:text-red-500 after:content-['*']">
                    Name
                </Label>

                <Input
                    id="name"
                    type="text"
                    name="name"
                    value={data.name}
                    className="mt-1 block w-full"
                    autoComplete="name"
                    onChange={onHandleChange}
                    required
                />

                <InputError message={errors.name} className="mt-2" />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="slug" className="after:text-red-500 after:content-['*']">
                    Slug
                </Label>

                <Input
                    id="slug"
                    type="text"
                    name="slug"
                    value={data.slug}
                    className="mt-1 block w-full"
                    autoComplete="slug"
                    onChange={onHandleChange}
                    required={false}
                />

                <InputError message={errors.slug} className="mt-2" />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="menu_icon">Icon (Lucide icon name)</Label>

                <Input
                    id="menu_icon"
                    type="text"
                    name="menu_icon"
                    value={data.menu_icon}
                    className="mt-1 block w-full"
                    autoComplete="menu_icon"
                    onChange={onHandleChange}
                />

                <InputError message={errors.menu_icon} className="mt-2" />
            </div>

            <div className="grid w-1/4 gap-2">
                <Label htmlFor="order_by" className="after:text-red-500 after:content-['*']">
                    Order
                </Label>

                <Input
                    id="order_by"
                    type="text"
                    name="order_by"
                    value={data.order_by}
                    className="mt-1 block w-full"
                    autoComplete="order_by"
                    onChange={onHandleChange}
                    required
                />

                <InputError message={errors.order_by} className="mt-2" />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="parent_id">Parent Menu</Label>
                <Select
                    id="parent_id"
                    allowClear
                    onChange={(value) => {
                        setData('parent_id', value ?? null);
                    }}
                    className="basic-single m-0 !h-9 w-full"
                    options={parentmenus}
                    placeholder="Select Menu"
                    value={data.parent_id ?? null}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="user_role">Role</Label>
                <Select
                    id="user_role"
                    mode="multiple"
                    allowClear
                    onChange={(value) => {
                        setData({ ...data, role: value || [] });
                    }}
                    className="basic-single m-0 !h-9 w-full"
                    options={roles}
                    placeholder="Select Role"
                    value={data.role || undefined}
                />
            </div>
        </MainForm>
    );
}
