import { Link, usePage } from "@inertiajs/react";
import { type PropsWithChildren } from "react";

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const { appSettings } = usePage().props as {
        appSettings: Record<string, string>;
    };
    const logoUrl = appSettings?.logo
        ? `/storage/${appSettings.logo}`
        : "/default/logo.png";
    return (
        <div
            className="flex min-h-svh flex-col items-center justify-center bg-background bg-cover bg-center bg-no-repeat p-6 md:p-10"
            style={{
                backgroundImage: `url('/images/bg.jpg')`,
            }}
        >
            <div className="w-full max-w-sm">
                <div className="flex flex-col">
                    <div className="flex flex-col items-center">
                        <Link
                            href="#"
                            className="flex flex-col items-center gap-2 font-medium"
                        >
                            <div className="mb-1 flex h-auto w-40 items-center justify-center rounded-md">
                                <img
                                    src={logoUrl}
                                    alt="Logo"
                                    className="container mx-auto my-4 h-auto"
                                />
                            </div>
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            <p className="text-center text-sm text-muted-foreground">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
