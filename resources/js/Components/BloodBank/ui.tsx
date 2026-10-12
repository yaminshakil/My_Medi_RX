import { Link } from '@inertiajs/react';

import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import {
    Card as ShadcnCard,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import {
    Select as ShadcnSelect,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------

export const cx = (...parts) => cn(...parts.filter(Boolean));

// Drops empty values so query strings stay short.
export const clean = (obj) =>
    Object.fromEntries(
        Object.entries(obj).filter(
            ([, value]) =>
                value !== '' && value !== null && value !== undefined
        )
    );

export const inputCls =
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50';

export const formatDateTime = (iso) =>
    iso
        ? new Date(iso).toLocaleString([], {
            dateStyle: 'medium',
            timeStyle: 'short',
        })
        : '—';

export const formatHoursLeft = (hours) => {
    if (hours < 0) return 'Expired';
    if (hours < 24) return `${hours}h left`;
    return `${Math.floor(hours / 24)}d left`;
};

// -----------------------------------------------------------------------------
// Badges and status pills
// -----------------------------------------------------------------------------

const tones = {
    slate: 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
    teal: 'border-teal-200 bg-teal-50 text-teal-800 dark:border-teal-900 dark:bg-teal-950 dark:text-teal-300',
    amber: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    rose: 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300',
    sky: 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-300',
};

export function Pill({ tone = 'slate', children, className, ...props }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium',
                tones[tone] ?? tones.slate,
                className
            )}
            {...props}
        >
            {children}
        </span>
    );
}

const statusTone = {
    available: 'teal',
    quarantined: 'amber',
    issued: 'sky',
    expired: 'rose',
    discarded: 'slate',
};

export function StatusPill({ status }) {
    const label = status
        ? status.charAt(0).toUpperCase() + status.slice(1)
        : 'Unknown';

    return <Pill tone={statusTone[status] ?? 'slate'}>{label}</Pill>;
}

const expiryTone = {
    ok: 'slate',
    warning: 'amber',
    critical: 'rose',
    expired: 'rose',
};

export function ExpiryPill({ state, hours }) {
    return (
        <Pill tone={expiryTone[state] ?? 'slate'}>
            {formatHoursLeft(hours)}
        </Pill>
    );
}

// -----------------------------------------------------------------------------
// Buttons
// -----------------------------------------------------------------------------

const buttonVariants = {
    primary: 'bg-rose-800 text-white hover:bg-rose-900',
    secondary:
        'border border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
    danger:
        'border border-rose-200 bg-background text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950',
};

export function Btn({
    variant = 'primary',
    href,
    className,
    type = 'button',
    children,
    ...props
}) {
    const variantClass = buttonVariants[variant] ?? buttonVariants.primary;

    if (href) {
        return (
            <Button
                asChild
                className={cn(variantClass, className)}
                {...props}
            >
                <Link href={href}>{children}</Link>
            </Button>
        );
    }

    return (
        <Button
            type={type}
            className={cn(variantClass, className)}
            {...props}
        >
            {children}
        </Button>
    );
}

// -----------------------------------------------------------------------------
// Cards
// -----------------------------------------------------------------------------

export function Card({ title, actions, children, className }) {
    return (
        <ShadcnCard className={cn('overflow-hidden', className)}>
            {title || actions ? (
                <CardHeader className="flex flex-row items-center justify-between gap-3 border-b px-5 py-4">
                    {title ? (
                        <CardTitle className="text-base font-semibold">
                            {title}
                        </CardTitle>
                    ) : (
                        <div />
                    )}

                    {actions}
                </CardHeader>
            ) : null}

            <CardContent
                className={cn('p-5', !(title || actions) && 'pt-5')}
            >
                {children}
            </CardContent>
        </ShadcnCard>
    );
}

// -----------------------------------------------------------------------------
// Form fields
// -----------------------------------------------------------------------------

export function Field({
    label,
    error,
    hint,
    children,
    className,
    id,
}) {
    return (
        <div className={cn('grid gap-2', className)}>
            {label ? (
                <Label htmlFor={id} className="text-sm font-medium">
                    {label}
                </Label>
            ) : null}

            {children}

            {hint && !error ? (
                <p className="text-xs text-muted-foreground">{hint}</p>
            ) : null}

            {error ? (
                <p className="text-sm font-medium text-destructive" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}

// -----------------------------------------------------------------------------
// Select
// Supports options: ['A+', 'B+'] or [{ value: 'A+', label: 'A+' }]
//
// Supports both shadcn onValueChange and legacy onChange handlers.
// -----------------------------------------------------------------------------

export function Select({
    options = [],
    placeholder = 'Select an option',
    className,
    value,
    onChange,
    onValueChange,
    name,
    required,
    disabled,
    ...props
}) {
    const normalizedOptions = options.map((option) =>
        typeof option === 'object'
            ? {
                value: String(option.value),
                label: String(option.label ?? option.value),
                disabled: option.disabled ?? false,
            }
            : {
                value: String(option),
                label: String(option),
                disabled: false,
            }
    );

    const handleValueChange = (nextValue) => {
        onValueChange?.(nextValue);

        // Preserve compatibility with existing native-select handlers.
        onChange?.({
            target: {
                name,
                value: nextValue,
            },
        });
    };

    return (
        <ShadcnSelect
            value={value === '' || value == null ? undefined : String(value)}
            onValueChange={handleValueChange}
            name={name}
            required={required}
            disabled={disabled}
            {...props}
        >
            <SelectTrigger className={cn('w-full', className)}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>

            <SelectContent>
                {normalizedOptions.map((option) => (
                    <SelectItem
                        key={option.value}
                        value={option.value}
                        disabled={option.disabled}
                    >
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </ShadcnSelect>
    );
}

// -----------------------------------------------------------------------------
// Pagination
// -----------------------------------------------------------------------------

export function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav
            className="flex flex-wrap items-center gap-1"
            aria-label="Pagination"
        >
            {links.map((link, index) => {
                const label = link.label
                    .replace(/&laquo;|&#171;/g, '«')
                    .replace(/&raquo;|&#187;/g, '»')
                    .replace(/&amp;/g, '&')
                    .replace(/<[^>]*>/g, '');

                const variant = link.active ? 'default' : 'outline';

                return link.url ? (
                    <Button
                        key={`${link.url}-${index}`}
                        asChild
                        size="sm"
                        variant={variant}
                        disabled={!link.url}
                        className={cn(
                            link.active &&
                            'border-rose-800 bg-rose-800 text-white hover:bg-rose-900'
                        )}
                    >
                        <Link href={link.url} preserveScroll>
                            {label}
                        </Link>
                    </Button>
                ) : (
                    <Button
                        key={`page-${index}`}
                        size="sm"
                        variant="outline"
                        disabled
                        aria-current={link.active ? 'page' : undefined}
                    >
                        {label}
                    </Button>
                );
            })}
        </nav>
    );
}

// -----------------------------------------------------------------------------
// Empty state and table utilities
// -----------------------------------------------------------------------------

export function EmptyState({ children }) {
    return (
        <div className="flex min-h-32 items-center justify-center rounded-lg border border-dashed p-6">
            <p className="text-center text-sm text-muted-foreground">
                {children}
            </p>
        </div>
    );
}

export const th =
    'h-10 px-3 text-left align-middle text-xs font-semibold text-muted-foreground';

export const td = 'px-3 py-3 align-middle text-sm text-foreground';
