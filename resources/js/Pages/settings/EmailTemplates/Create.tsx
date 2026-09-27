import InputError from '@/Components/input-error';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { Editor } from 'primereact/editor';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Create Template', href: '/settings/email-templates' }];

type TForm = {
    name: string;
    slug: string;
    subject: string;
    body: string;
};

export default function Create() {
    const { data, setData, post, processing, reset, errors } = useForm<TForm>({
        name: '',
        slug: '',
        subject: '',
        body: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('email-templates.store'), {
            onSuccess: () => reset(),
        });
    };

    const handleCancel = () => {
        router.get(route('email-templates.index'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Email Template" />
            <div className="flex h-full flex-1 flex-col items-center gap-4 overflow-x-auto rounded-xl p-4">
                <form onSubmit={submit} className="flex w-full flex-col gap-6 p-4 shadow-md sm:w-2/3 sm:rounded-lg">
                    <h1 className="text-xl font-bold">Add Template</h1>

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
                        <Label htmlFor="slug">Slug</Label>
                        <Input id="slug" className="w-full border" value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
                        {errors.slug && <InputError message={errors.slug} />}
                    </div>

                    {/* Slug */}
                    <div>
                        <Label htmlFor="subject">Subject</Label>
                        <Input id="subject" className="w-full border" value={data.subject} onChange={(e) => setData('subject', e.target.value)} />
                        {errors.subject && <InputError message={errors.subject} />}
                    </div>

                    {/* Body */}
                    <div>
                        <Label htmlFor="body">Body</Label>
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
                            Create
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
