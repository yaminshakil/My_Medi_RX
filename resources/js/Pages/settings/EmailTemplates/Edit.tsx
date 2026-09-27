import InputError from '@/Components/input-error';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Check, Copy, LoaderCircle } from 'lucide-react';
import { Editor } from 'primereact/editor';
import { FormEventHandler, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Edit Template', href: '/settings/email-templates' }];

type TForm = {
    name: string;
    slug: string;
    subject: string;
    body: string;
};

export default function Edit({ template }) {
    const [copied, setCopied] = useState('');

    const { data, setData, put, processing, reset, errors } = useForm<TForm>({
        name: template.name || '',
        slug: template.slug || '',
        subject: template.subject || '',
        body: template.body || '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('email-templates.update', template.id), {
            onSuccess: () => reset(),
        });
    };

    const handleCancel = () => {
        router.get(route('email-templates.index'));
    };

    const handleCopy = async (text) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(text);
            setTimeout(() => setCopied(''), 1500);
        } catch (err) {
            console.error('Failed to copy:', err);
            alert('Copy failed. Try manually!');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Email Template" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <div className="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
                    <table className="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                        <thead className="bg-[var(--base-color)] text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th className="px-6 py-4 text-white">Short Code</th>
                                <th className="px-6 py-4 text-right text-white">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            {template.shortcodes.map((item) => (
                                <tr key={item.code} className="border-b">
                                    <td className="flex items-center justify-between px-4 py-2">
                                        <span>{item.code}</span>
                                        <button
                                            type="button"
                                            onClick={() => handleCopy(item.code)}
                                            className="flex items-center gap-1 text-blue-500 hover:text-blue-700"
                                        >
                                            {copied === item.code ? (
                                                <>
                                                    <Check className="h-4 w-4 text-green-500" /> <span>Copied</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="h-4 w-4" /> <span>Copy</span>
                                                </>
                                            )}
                                        </button>
                                    </td>
                                    <td className="px-4 py-2 text-right">{item.desc}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <form role="form" onSubmit={submit} className="flex w-full flex-col gap-6 p-4 shadow-md sm:w-2/3 sm:rounded-lg">
                    <h1 className="text-xl font-bold">Edit Template</h1>

                    {/* Name */}
                    <div>
                        <Label htmlFor="name" className="after:text-red-500 after:content-['*']">
                            Name
                        </Label>
                        <Input id="name" className="w-full border" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {errors.name && <InputError message={errors.name} />}
                    </div>

                    {/* Slug */}
                    <div>
                        <Label htmlFor="slug" className="after:text-red-500 after:content-['*']">
                            Slug
                        </Label>
                        <Input id="slug" className="w-full border" value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
                        {errors.slug && <InputError message={errors.slug} />}
                    </div>

                    {/* Slug */}
                    <div>
                        <Label htmlFor="subject" className="after:text-red-500 after:content-['*']">
                            Subject
                        </Label>
                        <Input id="subject" className="w-full border" value={data.subject} onChange={(e) => setData('subject', e.target.value)} />
                        {errors.subject && <InputError message={errors.subject} />}
                    </div>

                    {/* Body */}
                    <div>
                        <Label htmlFor="body" className="after:text-red-500 after:content-['*']">
                            Body
                        </Label>
                        <Editor
                            id="body"
                            value={data.body}
                            onTextChange={(e) => {
                                setData('body', e.htmlValue);
                            }}
                            style={{ height: '220px' }}
                        />
                    </div>

                    {/* Submit */}
                    <div className="mt-4 flex justify-start gap-2">
                        <Button type="button" onClick={() => handleCancel()} className="mt-2 w-fit" disabled={processing}>
                            Cancel
                        </Button>
                        <Button type="submit" className="mt-2 w-fit" disabled={processing}>
                            {processing && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
                            Update
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
