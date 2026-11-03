import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/Components/input-error';
import TextLink from '@/Components/text-link';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AuthLayout from '@/Layouts/auth-layout';
import { Eye, EyeOff, Lock } from 'lucide-react';
import React, { useState } from 'react';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

     const togglePasswordVisibility = () => {
    setShowPassword((prev) => !prev);
  };

    return (
        <AuthLayout title="" description="">
            <Head title="Log in" />
            <Card className="w-full max-w-sm bg-[var(--base-color)]">
                <CardHeader>
                    <CardTitle className="text-center text-white">Login to your account</CardTitle>
                    <CardDescription className="text-center text-white">Welcome, please sign in to your dashboard</CardDescription>
                </CardHeader>
                <CardContent>
                    <form className="flex flex-col gap-6" onSubmit={submit}>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email" className="text-white">
                                    Email address
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password" className="text-white">
                                        Password
                                    </Label>
                                    {canResetPassword && (
                                        <TextLink href={route('password.request')} className="ml-auto text-sm text-white" tabIndex={5}>
                                            Forgot password?
                                        </TextLink>
                                    )}
                                </div>
                                <div className="relative flex items-center">
                                <Input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Password"
                                />
                                {/* Right Toggle Button (Eye / EyeOff) */}
                                        <button
                                          type="button"
                                          onClick={togglePasswordVisibility}
                                          aria-label={showPassword ? 'Hide password' : 'Show password'}
                                          className="absolute right-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors focus:outline-none p-1 rounded-md"
                                        >
                                          {showPassword ? (
                                            <EyeOff className="w-5 h-5" />
                                          ) : (
                                            <Eye className="w-5 h-5" />
                                          )}
                                        </button>
                                <InputError message={errors.password} />
                                </div>
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onClick={() => setData('remember', !data.remember)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember" className="text-white">
                                    Remember me
                                </Label>
                            </div>

                            <Button type="submit" className="cursor-pointe mt-4 w-full text-white" tabIndex={4} disabled={processing}>
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Log in
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground text-white">
                            Don't have an account?{' '}
                            <TextLink href={route('register')} className="text-white" tabIndex={5}>
                                Sign up
                            </TextLink>
                        </div>
                    </form>
                </CardContent>
            </Card>
            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
