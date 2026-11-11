import Table from '@/Components/Table';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Experiences ', href: '/doctor/experiences' }];

export default function Index() {
    const { experiences, flash } = usePage().props;

    const [showForm, setShowForm] = useState(false);
    const [experience, setExperience] = useState(false);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData, post, processing, reset, errors } = useForm({
        hospital_name: '',
        position: '',
        from_year: '',
        to_year: '',
        description: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        if (experience) {
            post(route('doctor.experiences.update', experience.id), {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                },
            });
        } else {
            post(route('doctor.experiences.store'), {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                },
            });
        }
    };

    const handleDelete = (item) => {
        if (confirm('Are you sure you want to delete this experience?')) {
            post(route('doctor.experiences.destroy', item.id), {
                method: 'delete',
            });
        }
    };

    const handleEdit = (item) => {
        const editExperience = experiences.find((exp) => exp.id === item.id);
        setExperience(editExperience);
        setShowForm(true);
        setData((prev) => ({
            ...prev,
            hospital_name: editExperience.hospital_name, // File object
            position: editExperience.position,
            from_year: editExperience.from_year,
            to_year: editExperience.to_year,
            description: editExperience.description,
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

    const columns = [
        { key: '#', label: '#', render: (item) => item.id },
        { key: 'hospital_name', label: 'Hospital' },
        { key: 'position', label: 'Position' },
        { key: 'from_year', label: 'From' },
        { key: 'to_year', label: 'To' },
        { key: 'description', label: 'Description' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Experiences" />
            <ToastContainer />
            <div className="mx-auto w-full space-y-8 p-4">
                <Card className="mx-auto w-full shadow-md">
                    <CardHeader className="flex items-center justify-between">
                        <CardTitle className="text-xl font-bold">Doctor Experiences</CardTitle>
                        {canCreate && <Button onClick={() => setShowForm(!showForm)}>{showForm ? 'Close' : 'Add Experience'}</Button>}
                    </CardHeader>
                    <CardContent>
                        {showForm && (
                            <form role="form" onSubmit={handleSubmit} className="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                                {/* Full width field */}
                                <div className="col-span-1 grid w-full gap-2 md:col-span-2">
                                    <Label htmlFor="hospital_name" className="after:text-red-500 after:content-['*']">
                                        Hospital Name
                                    </Label>
                                    <Input
                                        id="hospital_name"
                                        type="text"
                                        value={data.hospital_name}
                                        onChange={(e) => setData('hospital_name', e.target.value)}
                                        className="w-full rounded border p-2"
                                        required
                                    />
                                    {errors.hospital_name && <div className="mt-1 text-sm text-red-500">{errors.hospital_name}</div>}
                                </div>

                                {/* Half-width fields */}
                                <div className="flex w-full flex-col gap-2">
                                    <Label htmlFor="position" className="after:text-red-500 after:content-['*']">
                                        Position
                                    </Label>
                                    <Input
                                        id="position"
                                        type="text"
                                        value={data.position}
                                        onChange={(e) => setData('position', e.target.value)}
                                        className="w-full rounded border p-2"
                                        required
                                    />
                                </div>

                                <div className="flex w-full flex-col gap-2">
                                    <Label htmlFor="from_year" className="after:text-red-500 after:content-['*']">
                                        From Year
                                    </Label>
                                    <Input
                                        id="from_year"
                                        type="text"
                                        value={data.from_year}
                                        onChange={(e) => setData('from_year', e.target.value)}
                                        className="w-full rounded border p-2"
                                        placeholder="e.g. 2015"
                                        required
                                    />
                                </div>

                                <div className="flex w-full flex-col gap-2">
                                    <Label htmlFor="to_year" className="after:text-red-500 after:content-['*']">
                                        To Year
                                    </Label>
                                    <Input
                                        id="to_year"
                                        type="text"
                                        value={data.to_year}
                                        onChange={(e) => setData('to_year', e.target.value)}
                                        className="w-full rounded border p-2"
                                        placeholder="e.g. 2020 or Present"
                                        required
                                    />
                                </div>

                                {/* Full width */}
                                <div className="col-span-1 flex w-full flex-col gap-2 md:col-span-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        className="w-full rounded border p-2"
                                        rows={3}
                                    />
                                </div>

                                {/* Submit button */}
                                <div className="col-span-1 flex w-full justify-start md:col-span-2">
                                    <Button type="submit" data-testid="save-button" disabled={processing}>
                                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                        {experience ? 'Update' : 'Save'}
                                    </Button>
                                </div>
                            </form>
                        )}

                        <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <Table
                                items={experiences}
                                columns={columns}
                                canEdit={canEdit}
                                canDelete={canDelete}
                                onEdit={handleEdit}
                                onDelete={handleDelete}
                                actionHeadClass="border p-4 w-24 text-center"
                                emptyMessage="No experiences added yet."
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
