import MainForm from '@/Components/Form/MainForm';
import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { router, usePage } from '@inertiajs/react';
import { Select } from 'antd';
import type { Language } from '@/types';
import { AppPageProps } from '@/types';

interface SharedProps {
    languages: Language[];
}

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
    translations?: {
        name?: Record<string, string>;
    };
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
    console.log(data);
    const { languages } = usePage<SharedProps>().props;
    const { translations } = usePage<AppPageProps>().props;
    const onHandleChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const handleCancel = () => {
        router.get(route('menus.index'));
    };

    /**
     * Update translation value
     */
    const updateTranslation = (
        locale: string,
        value: string,
    ) => {
        setData('translations', {
            ...(data.translations ?? {}),

            name: {
                ...(data.translations?.name ?? {}),
                [locale]: value,
            },
        });
    };

    return (
        <MainForm handleSubmit={handleSubmit} handleCancel={handleCancel} processing={processing} submitTitle={submitTitle}>
            <h1 className="items-start !text-left font-extrabold">{heading}</h1>
            <div className="grid gap-2">
                <Label htmlFor="name" className="after:text-red-500 after:content-['*']">
                    {translations.common.name}
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

            {/* =========================
                            Dynamic Translations
                        ========================== */}
            <div className="grid gap-2 rounded-lg border p-4">
                <div>
                    <h3 className="font-semibold">
                        {translations.common.Translations}
                    </h3>

                    <p className="text-sm text-muted-foreground">
                        {translations.common.Enter_the_specialty_name_in_each_supported_language}
                    </p>
                </div>

                {languages.map((language) => (
                    console.log(data.translations),
                    <div
                        key={language.code}
                        className="grid gap-2"
                    >
                        <Label
                            htmlFor={`translation_name_${language.code}`}
                        >
                            {translations.common.name} ({language.name})
                        </Label>

                        <Input
                            id={`translation_name_${language.code}`}
                            dir={
                                language.code === 'ar'
                                    ? 'rtl'
                                    : 'ltr'
                            }
                            className="w-full border"
                            value={
                                data.translations?.name?.[
                                language.code
                                ] ?? ''
                            }
                            onChange={(e) =>
                                updateTranslation(
                                    language.code,
                                    e.target.value,
                                )
                            }
                        />

                        {errors[
                            `translations.name.${language.code}`
                        ] && (
                                <InputError
                                    message={
                                        errors[
                                        `translations.name.${language.code}`
                                        ]
                                    }
                                />
                            )}
                    </div>
                ))}
            </div>

            <div className="grid gap-2">
                <Label htmlFor="slug" className="after:text-red-500 after:content-['*']">
                    {translations.common.slug}
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
                <Label htmlFor="menu_icon">
                    {translations.common.icon} ({translations.common.lucid_icon_name})
                </Label>

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
                    {translations.common.order}
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
                <Label htmlFor="parent_id">
                    {translations.common.parent_menu}
                </Label>
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
                <Label htmlFor="user_role">
                    {translations.common.role}
                </Label>
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
