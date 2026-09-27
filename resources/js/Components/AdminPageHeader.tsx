import { Button } from '@/Components/ui/button';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';

interface AdminPageHeaderProps {
    breadcrumbs?: BreadcrumbItemType[];
    title?: string;
    description?: string;
    actions?: ReactNode;
    toolbar?: ReactNode;
    backHref?: string | null;
    filters?: ReactNode;
}

export function AdminPageHeader({ breadcrumbs = [], title, description, actions, toolbar, backHref, filters }: AdminPageHeaderProps) {
    const fallbackBackHref = breadcrumbs.length > 1 ? breadcrumbs[breadcrumbs.length - 2]?.href : null;
    const resolvedTitle = title || breadcrumbs[breadcrumbs.length - 1]?.title || 'Admin';

    const handleBack = () => {
        if (backHref) {
            router.visit(backHref);
            return;
        }

        if (fallbackBackHref) {
            router.visit(fallbackBackHref);
            return;
        }

        if (typeof window !== 'undefined' && window.history.length > 1) {
            window.history.back();
            return;
        }

        router.visit(route('dashboard'));
    };

    return (
        <div
            className="mx-2 rounded-xl p-4 text-white shadow-sm md:mx-0"
            style={{
                background: 'linear-gradient(135deg, var(--base-color) 0%, color-mix(in srgb, var(--base-color) 70%, black) 100%)',
            }}
        >
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-wrap items-center gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={handleBack}
                        className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Back
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">{resolvedTitle}</h1>
                        {description && <p className="mt-0.5 text-sm text-white/70">{description}</p>}
                    </div>
                </div>

                {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
            </div>

            {toolbar && <div className="mt-4 rounded-lg bg-white p-4 text-slate-900 shadow-sm">{toolbar}</div>}
            {filters && <div className="mt-4 rounded-lg bg-white p-4 text-slate-900 shadow-sm">{filters}</div>}
        </div>
    );
}
