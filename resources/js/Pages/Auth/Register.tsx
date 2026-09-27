import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/Components/input-error';
import TextLink from '@/Components/text-link';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthLayout from '@/Layouts/auth-layout';
import { Select } from 'antd';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    registration_type: string;
};

const registrationtype = [
    { value: 'Patient', label: 'User or Patient' },
    { value: 'Doctor', label: 'Doctor' },
];

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        registration_type: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="" description="">
            <Head title="Register" />
            <Card className="w-full max-w-sm bg-[var(--base-color)]">
                <CardHeader>
                    <CardTitle className="text-center text-white">Create an account</CardTitle>
                    <CardDescription className="text-center text-white">See your growth and get consulting support!</CardDescription>
                </CardHeader>
                <CardContent>
                    <form role="form" className="flex flex-col gap-6" onSubmit={submit}>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="text-white">
                                    Name
                                </Label>
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
                                    placeholder="Full name"
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email" className="text-white">
                                    Email address
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    disabled={processing}
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password" className="text-white">
                                    Password
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    disabled={processing}
                                    placeholder="Password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation" className="text-white">
                                    Confirm password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
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
                                <Label htmlFor="registration_type" className="text-white">
                                    Registration Type
                                </Label>

                                <Select
                                    id="registration_type"
                                    placeholder="Select Registration Type"
                                    showSearch
                                    optionFilterProp="label"
                                    value={data.registration_type || undefined}
                                    onChange={(value) => setData('registration_type', value)}
                                    className="basic-single m-0 !h-[36px] w-full rounded-md disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50"
                                    options={registrationtype}
                                    disabled={processing}
                                    tabIndex={5}
                                />

                                <InputError message={errors.registration_type} className="mt-2 text-red-700" />
                            </div>

                            <Button type="submit" className="mt-2 w-full cursor-pointer text-white" tabIndex={6} disabled={processing}>
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Create account
                            </Button>
                        </div>

                        <div className="text-center text-sm text-white">
                            Already have an account?{' '}
                            <TextLink href={route('login')} className="text-white" tabIndex={6}>
                                Log in
                            </TextLink>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AuthLayout>
    );
}
