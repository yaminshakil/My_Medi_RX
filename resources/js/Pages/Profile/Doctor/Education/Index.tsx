import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Edit, LoaderCircle, Trash2 } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Educations ', href: '/doctor/educations' }];

export default function Index() {
    const { props } = usePage();
    const { educations = [], flash } = props;
    const [education, setEduccation] = useState(null);

    const { data, setData, post, processing, reset, errors } = useForm({
        degree: '',
        institute: '',
        year: '',
        country: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        if (education) {
            post(route('doctor.educations.update', education.id), {
                onSuccess: () => {
                    reset();
                    setEduccation(null);
                },
            });
        } else {
            post(route('doctor.educations.store'), {
                onSuccess: () => reset(),
            });
        }
    };

    const handleDelete = (id) => {
        if (!confirm('Are you sure you want to delete this education?')) return;
        router.delete(route('doctor.educations.destroy', id));
    };

    const handleEdit = (id) => {
        const editEducation = educations.find((edu) => edu.id === id);
        setEduccation(editEducation);
        setData((prev) => ({
            ...prev,
            degree: editEducation.degree, // File object
            institute: editEducation.institute,
            year: editEducation.year,
            country: editEducation.country,
        }));
    };

    useEffect(() => {
        if (flash.message.success) {
            toast.success(flash.message.success);
        }
        if (flash.message.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Doctor Educatios" />
            <ToastContainer />
            <div className="mx-auto w-full space-y-4 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">{education ? 'Edit' : 'Add'} Doctor Education</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form role="form" onSubmit={handleSubmit} className="grid gap-4 md:grid-cols-2">
                            <div className="col-span-2 md:col-span-1">
                                <Label className="after:text-red-500 after:content-['*']">Degree</Label>
                                <Input
                                    type="text"
                                    placeholder="Degree"
                                    value={data.degree}
                                    onChange={(e) => setData('degree', e.target.value)}
                                    required
                                />
                                {errors.degree && <div className="text-sm text-red-500">{errors.degree}</div>}
                            </div>

                            <div className="col-span-2 md:col-span-1">
                                <Label className="after:text-red-500 after:content-['*']">Institute</Label>
                                <Input
                                    type="text"
                                    placeholder="Institute"
                                    value={data.institute}
                                    onChange={(e) => setData('institute', e.target.value)}
                                    required
                                />
                                {errors.institute && <div className="text-sm text-red-500">{errors.institute}</div>}
                            </div>

                            <div className="col-span-2 md:col-span-1">
                                <Label className="after:text-red-500 after:content-['*']">Year</Label>
                                <Input type="text" placeholder="Year" value={data.year} onChange={(e) => setData('year', e.target.value)} required />
                                {errors.year && <div className="text-sm text-red-500">{errors.year}</div>}
                            </div>

                            <div className="col-span-2 md:col-span-1">
                                <Label className="after:text-red-500 after:content-['*']">Country</Label>
                                <Input
                                    type="text"
                                    placeholder="Country"
                                    value={data.country}
                                    onChange={(e) => setData('country', e.target.value)}
                                    required
                                />
                                {errors.country && <div className="text-sm text-red-500">{errors.country}</div>}
                            </div>

                            <div className="col-span-2">
                                <Button type="submit" disabled={processing}>
                                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    {education ? 'Update Education' : 'Add Education'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Existing Educations</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {educations.length === 0 ? (
                            <p className="text-gray-500">No education records found.</p>
                        ) : (
                            <div className="space-y-3">
                                {educations.map((edu) => (
                                    <div key={edu.id} className="flex items-center justify-between rounded-lg border p-3">
                                        <div>
                                            <div className="font-semibold">{edu.degree}</div>
                                            <div className="text-sm text-gray-600">
                                                {edu.institute} {edu.year && `(${edu.year})`} {edu.country && `- ${edu.country}`}
                                            </div>
                                        </div>
                                        <div className="flex flex-wrap gap-2">
                                            <Button
                                                onClick={() => handleEdit(edu?.id)}
                                                className="inline-flex w-full cursor-pointer items-center text-white md:w-auto"
                                            >
                                                <Edit className="mr-1 h-4 w-4" /> Edit
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                onClick={() => handleDelete(edu.id)}
                                                className="cursor-pointer"
                                            >
                                                <Trash2 className="mr-1 h-4 w-4" />
                                                Delete
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
