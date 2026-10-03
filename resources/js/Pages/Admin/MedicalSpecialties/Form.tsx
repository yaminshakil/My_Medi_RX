import MainForm from '@/Components/Form/MainForm';
import InputError from '@/Components/input-error';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { router, usePage } from '@inertiajs/react';
import { Select } from 'antd';
import type { Language } from '@/types';

const { Option } = Select;

interface SharedProps {
    languages: Language[];
}

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
    parent_id: number | null;

    translations?: {
        name?: Record<string, string>;
    };
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

export default function Form({
    parents,
    data,
    setData,
    handleSubmit,
    processing,
    errors,
    submitTitle = 'Create',
    heading,
}: FormProps) {
    const { languages } = usePage<SharedProps>().props;

    const handleCancel = () => {
        router.get(route('medical-specialties.index'));
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
        <MainForm
            handleSubmit={handleSubmit}
            handleCancel={handleCancel}
            processing={processing}
            submitTitle={submitTitle}
        >
            <h1 className="items-start !text-left font-extrabold">
                {heading}
            </h1>

            {/* =========================
                Default Name
            ========================== */}
            <div className="grid gap-2">
                <Label
                    htmlFor="name"
                    className="after:text-red-500 after:content-['*']"
                >
                    Name
                </Label>

                <Input
                    id="name"
                    className="w-full border"
                    value={data.name ?? ''}
                    onChange={(e) =>
                        setData('name', e.target.value)
                    }
                />

                {errors.name && (
                    <InputError message={errors.name} />
                )}
            </div>

            {/* =========================
                Dynamic Translations
            ========================== */}
            <div className="grid gap-4 rounded-lg border p-4">
                <div>
                    <h3 className="font-semibold">
                        Translations
                    </h3>

                    <p className="text-sm text-muted-foreground">
                        Enter the specialty name in each
                        supported language.
                    </p>
                </div>

                {languages.map((language) => (
                    <div
                        key={language.code}
                        className="grid gap-2"
                    >
                        <Label
                            htmlFor={`translation_name_${language.code}`}
                        >
                            Name ({language.name})
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

            {/* =========================
                Icon
            ========================== */}
            <div className="grid gap-2">
                <Label htmlFor="icon">
                    Icon
                </Label>

                <Input
                    id="icon"
                    className="w-full border"
                    value={data.icon ?? ''}
                    onChange={(e) =>
                        setData('icon', e.target.value)
                    }
                />
            </div>

            {/* =========================
                Description
            ========================== */}
            <div className="grid gap-2">
                <Label htmlFor="description">
                    Description
                </Label>

                <Textarea
                    id="description"
                    className="w-full border"
                    value={data.description ?? ''}
                    onChange={(e) =>
                        setData(
                            'description',
                            e.target.value,
                        )
                    }
                />
            </div>

            {/* =========================
                Parent
            ========================== */}
            <div className="grid gap-2">
                <Label htmlFor="parent_id">
                    Parent
                </Label>

                <Select
                    id="parent_id"
                    allowClear
                    className="w-full"
                    value={
                        data.parent_id ?? undefined
                    }
                    onChange={(value) =>
                        setData(
                            'parent_id',
                            value ?? null,
                        )
                    }
                    placeholder="Select parent specialty"
                >
                    {Object.entries(parents).map(
                        ([id, name]) => (
                            <Option
                                key={id}
                                value={Number(id)}
                            >
                                {name}
                            </Option>
                        ),
                    )}
                </Select>
            </div>

            {/* =========================
                Surgical
            ========================== */}
            <div className="flex items-center gap-2">
                <Input
                    id="is_surgical"
                    className="w-6"
                    type="checkbox"
                    checked={Boolean(
                        data.is_surgical,
                    )}
                    onChange={(e) =>
                        setData(
                            'is_surgical',
                            e.target.checked,
                        )
                    }
                />

                <Label htmlFor="is_surgical">
                    Surgical?
                </Label>
            </div>
        </MainForm>
    );
}
