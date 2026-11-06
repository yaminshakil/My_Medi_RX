import { Head, useForm } from "@inertiajs/react";
import { LoaderCircle } from "lucide-react";
import { FormEventHandler } from "react";

import InputError from "@/Components/input-error";
import TextLink from "@/Components/text-link";
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import AuthLayout from "@/Layouts/auth-layout";
import { Select } from "antd";
import { Eye, EyeOff, Lock } from "lucide-react";
import React, { useState } from "react";

type RegisterForm = {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
    registration_type: string;
};

const registrationtype = [
    { value: "Patient", label: "User or Patient" },
    { value: "Doctor", label: "Doctor" },
];

interface RoleOption {
    value: string;
    label: string;
}

interface Props {
    roles: RoleOption[];
}

export default function Register({ roles }: Props) {
    const [password, setPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<
        Required<RegisterForm>
    >({
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        password_confirmation: "",
        registration_type: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("register"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    const togglePasswordVisibility = () => {
        setShowPassword((prev) => !prev);
    };

    return (
        <AuthLayout title="" description="">
            <Head title="Register" />
            <Card className="w-full max-w-sm bg-[var(--base-color)]">
                <CardHeader>
                    <CardTitle className="text-center text-white">
                        Create an account
                    </CardTitle>
                    <CardDescription className="text-center text-white">
                        See your growth and get consulting support!
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        role="form"
                        className="flex flex-col gap-6"
                        onSubmit={submit}
                    >
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="text-white">
                                    First Name
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    value={data.first_name}
                                    onChange={(e) =>
                                        setData("first_name", e.target.value)
                                    }
                                    disabled={processing}
                                    placeholder="First name"
                                />
                                <InputError
                                    message={errors.first_name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label
                                    htmlFor="last_name"
                                    className="text-white"
                                >
                                    Last Name
                                </Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    required
                                    tabIndex={2}
                                    autoComplete="last-name"
                                    value={data.last_name}
                                    onChange={(e) =>
                                        setData("last_name", e.target.value)
                                    }
                                    disabled={processing}
                                    placeholder="Last name"
                                />
                                <InputError
                                    message={errors.last_name}
                                    className="mt-2"
                                />
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
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    disabled={processing}
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label
                                    htmlFor="password"
                                    className="text-white"
                                >
                                    Password
                                </Label>
                                <div className="relative flex items-center">
                                    <Input
                                        id="password"
                                        type={
                                            showPassword ? "text" : "password"
                                        }
                                        required
                                        tabIndex={3}
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                        disabled={processing}
                                        placeholder="Password"
                                    />
                                    {/* Right Toggle Button (Eye / EyeOff) */}
                                    <button
                                        type="button"
                                        onClick={togglePasswordVisibility}
                                        aria-label={
                                            showPassword
                                                ? "Hide password"
                                                : "Show password"
                                        }
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

                            <div className="grid gap-2">
                                <Label
                                    htmlFor="password_confirmation"
                                    className="text-white"
                                >
                                    Confirm password
                                </Label>
                                <div className="relative flex items-center">
                                    <Input
                                        id="password_confirmation"
                                        type={
                                            showPassword ? "text" : "password"
                                        }
                                        required
                                        tabIndex={4}
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={(e) =>
                                            setData(
                                                "password_confirmation",
                                                e.target.value,
                                            )
                                        }
                                        disabled={processing}
                                        placeholder="Confirm password"
                                    />
                                    {/* Right Toggle Button (Eye / EyeOff) */}
                                    <button
                                        type="button"
                                        onClick={togglePasswordVisibility}
                                        aria-label={
                                            showPassword
                                                ? "Hide password"
                                                : "Show password"
                                        }
                                        className="absolute right-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors focus:outline-none p-1 rounded-md"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="w-5 h-5" />
                                        ) : (
                                            <Eye className="w-5 h-5" />
                                        )}
                                    </button>
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label
                                    htmlFor="registration_type"
                                    className="text-white"
                                >
                                    Registration Type
                                </Label>

                                <Select
                                    id="registration_type"
                                    placeholder="Select Registration Type"
                                    showSearch
                                    optionFilterProp="label"
                                    value={data.registration_type || undefined}
                                    onChange={(value) =>
                                        setData("registration_type", value)
                                    }
                                    className="basic-single m-0 !h-[36px] w-full rounded-md disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50"
                                    options={roles}
                                    disabled={processing}
                                    tabIndex={5}
                                />

                                <InputError
                                    message={errors.registration_type}
                                    className="mt-2 text-red-700"
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full cursor-pointer text-white"
                                tabIndex={6}
                                disabled={processing}
                            >
                                {processing && (
                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                )}
                                Create account
                            </Button>
                        </div>

                        <div className="text-center text-sm text-white">
                            Already have an account?{" "}
                            <TextLink
                                href={route("login")}
                                className="text-white"
                                tabIndex={6}
                            >
                                Log in
                            </TextLink>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AuthLayout>
    );
}
