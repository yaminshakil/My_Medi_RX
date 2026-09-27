import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { AdminPageHeader } from '@/Components/AdminPageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import {
    BadgeCheck,
    Calendar,
    CreditCard,
    Download,
    Globe,
    Mail,
    MapPin,
    Phone,
    Receipt,
    RefreshCw,
    ShoppingCart,
    UserIcon,
} from 'lucide-react';

interface Role {
    id: number;
    name: string;
}

interface Billing {
    id: number;
    business_name: string | null;
    first: string | null;
    last: string | null;
    email: string;
    phone: string | null;
    website: string | null;
    tax_id: string | null;
    address_street: string | null;
    address_apt: string | null;
    address_city: string | null;
    address_state: string | null;
    address_zip: string | null;
    address_country: string | null;
    address_country_code: string | null;
}

interface Subscription {
    id: number;
    subscription_id: number;
    plan_title: string;
    renewal_amount: number;
    initial_amount: number;
    billing_cycle: string | null;
    is_active: boolean;
    renewal_date: string | null;
    currency: string | null;
    cancelled_at: string | null;
    is_trial: boolean;
    trial_ends: string | null;
    is_free_trial: boolean;
    freemius_created_at: string | null;
    payment_method: { type: string } | null;
}

interface Payment {
    id: number;
    freemius_payment_id: number;
    gross: number;
    gateway_fee: number | null;
    vat: number | null;
    currency: string | null;
    is_renewal: boolean;
    gateway: string | null;
    payment_method: string | null;
    plan_title: string | null;
    country_code: string | null;
    invoice_url: string | null;
    freemius_created_at: string | null;
}

interface LicenseActivation {
    id: number;
    uid: string;
    title: string | null;
    version: string | null;
    url: string | null;
    license_plan_name: string | null;
    plugin_id: string | null;
    activated_at: string | null;
}

interface UserData {
    id: number;
    uuid: string;
    name: string;
    first_name: string | null;
    last_name: string | null;
    email: string;
    mobile: string | null;
    status: string;
    email_verified_at: string | null;
    avatar: string | null;
    created_at: string;
    roles: Role[];
    freemius_billing: Billing | null;
    subscriptions: Subscription[];
    freemius_payment: Payment[];
    license_activations: LicenseActivation[];
}

interface ShowProps {
    user: UserData;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/admin/users' },
    { title: 'User Details', href: '' },
];

function InfoRow({ label, value, icon: Icon }: { label: string; value: string | null | undefined; icon?: React.ElementType }) {
    if (!value) return null;
    return (
        <div className="flex items-start gap-2 py-1.5">
            {Icon && <Icon className="mt-0.5 size-4 shrink-0 text-muted-foreground" />}
            <div className="min-w-0 flex-1">
                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">{label}</p>
                <p className="text-sm break-words">{value}</p>
            </div>
        </div>
    );
}

function StatusBadge({ status, label }: { status: string; label: string }) {
    return (
        <Badge variant={status == 'active' ? 'default' : status == 'suspended' ? 'secondary' : 'destructive'} className="text-xs">
            {status == 'active' ? label : status == 'suspended' ? 'Suspended' : `Inactive`}
        </Badge>
    );
}

function formatDate(date: string | null | undefined): string {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(date: string | null | undefined): string {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

export default function Show({ user }: ShowProps) {

    const billing = user.freemius_billing;
    const subscriptions = user.subscriptions || [];
    const payments = user.freemius_payment || [];
    const activations = user.license_activations || [];

    const totalSpent = payments.reduce((sum, p) => sum + Number(p.gross || 0), 0);
    const activeSubscriptions = subscriptions.filter((s) => s.is_active).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User - ${user.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description="View full user profile, subscriptions, payments and license activations"
                    actions={
                        <Button variant="outline" size="sm" onClick={() => router.visit(route('users.edit', user.id))} className="border-white/30 bg-white/10 text-white hover:bg-white/20 hover:text-white">
                            Edit User
                        </Button>
                    }
                />
                {/* Smart Header */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                            <div className="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                                <Avatar className="size-20 border-2 border-white shadow-md">
                                    <AvatarImage src={user.avatar || undefined} alt={user.name} />
                                    <AvatarFallback className="bg-primary text-xl font-bold text-primary-foreground">
                                        {getInitials(user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="space-y-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl font-bold">{user.name}</h1>
                                        {user.email_verified_at && (
                                            <Badge variant="outline" className="gap-1 border-green-200 text-green-700">
                                                <BadgeCheck className="size-3" />
                                                Verified
                                            </Badge>
                                        )}
                                        <StatusBadge status={user.status} label="Active" />
                                    </div>
                                    <div className="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <Mail className="size-3.5" />
                                            {user.email}
                                        </span>
                                        {user.mobile && (
                                            <span className="flex items-center gap-1">
                                                <Phone className="size-3.5" />
                                                {user.mobile}
                                            </span>
                                        )}
                                        <span className="flex items-center gap-1">
                                            <Calendar className="size-3.5" />
                                            Joined {formatDate(user.created_at)}
                                        </span>
                                    </div>
                                    {user.roles.length > 0 && (
                                        <div className="flex flex-wrap gap-1.5 pt-1">
                                            {user.roles.map((role) => (
                                                <Badge key={role.id} variant="secondary" className="capitalize">
                                                    <UserIcon className="mr-1 size-3" />
                                                    {role.name}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                            <div className="flex gap-2">
                            </div>
                        </div>

                        {/* Quick Stats */}
                        <Separator className="my-4" />
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div className="text-center">
                                <p className="text-2xl font-bold">{subscriptions.length}</p>
                                <p className="text-xs text-muted-foreground">Subscriptions</p>
                            </div>
                            <div className="text-center">
                                <p className="text-2xl font-bold">{activeSubscriptions}</p>
                                <p className="text-xs text-muted-foreground">Active</p>
                            </div>
                            <div className="text-center">
                                <p className="text-2xl font-bold">{payments.length}</p>
                                <p className="text-xs text-muted-foreground">Payments</p>
                            </div>
                            <div className="text-center">
                                <p className="text-2xl font-bold">{activations.length}</p>
                                <p className="text-xs text-muted-foreground">Activations</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Billing Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CreditCard className="size-5" />
                                Billing Information
                            </CardTitle>
                            <CardDescription>Freemius billing details and address</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {billing ? (
                                <div className="space-y-0 divide-y">
                                    {(billing.business_name || billing.first) && (
                                        <div className="pb-2">
                                            {billing.business_name && <InfoRow label="Business" value={billing.business_name} />}
                                            {(billing.first || billing.last) && (
                                                <InfoRow label="Name" value={`${billing.first || ''} ${billing.last || ''}`.trim()} icon={UserIcon} />
                                            )}
                                        </div>
                                    )}
                                    <div className="py-2">
                                        <InfoRow label="Email" value={billing.email} icon={Mail} />
                                        <InfoRow label="Phone" value={billing.phone} icon={Phone} />
                                        <InfoRow label="Website" value={billing.website} icon={Globe} />
                                        <InfoRow label="Tax ID" value={billing.tax_id} />
                                    </div>
                                    {(billing.address_street || billing.address_city) && (
                                        <div className="pt-2">
                                            <InfoRow
                                                label="Address"
                                                value={[
                                                    billing.address_street,
                                                    billing.address_apt,
                                                    billing.address_city,
                                                    billing.address_state,
                                                    billing.address_zip,
                                                    billing.address_country,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ')}
                                                icon={MapPin}
                                            />
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">No billing information available</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Subscriptions */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <RefreshCw className="size-5" />
                                Subscriptions
                            </CardTitle>
                            <CardDescription>{subscriptions.length} subscription(s) found</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {subscriptions.length > 0 ? (
                                <div className="space-y-4">
                                    {subscriptions.map((sub) => (
                                        <div key={sub.id} className="space-y-2 rounded-lg border bg-muted/50 p-4">
                                            <div className="flex items-center justify-between">
                                                <p className="font-semibold">{sub.plan_title}</p>
                                                <StatusBadge active={sub.is_active} label="Active" />
                                            </div>
                                            <div className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-muted-foreground">
                                                <span>
                                                    Initial: {sub.currency?.toUpperCase()} {sub.initial_amount}
                                                </span>
                                                <span>
                                                    Renewal: {sub.currency?.toUpperCase()} {sub.renewal_amount}
                                                </span>
                                                <span>Cycle: {sub.billing_cycle || '-'}</span>
                                                <span>Renewal Date: {formatDate(sub.renewal_date)}</span>
                                                {sub.is_trial && (
                                                    <>
                                                        <span className="text-amber-600">Trial</span>
                                                        <span>Ends: {formatDate(sub.trial_ends)}</span>
                                                    </>
                                                )}
                                                {sub.cancelled_at && (
                                                    <span className="col-span-2 text-red-600">Cancelled: {formatDate(sub.cancelled_at)}</span>
                                                )}
                                            </div>
                                            {sub.payment_method?.type && (
                                                <p className="text-xs text-muted-foreground">Payment: {sub.payment_method.type}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">No subscriptions found</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Payments */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        <Receipt className="size-5" />
                                        Payments
                                    </CardTitle>
                                    <CardDescription>
                                        {payments.length} payment(s) &middot; Total spent: {payments[0]?.currency?.toUpperCase() || 'USD'}{' '}
                                        {(Number(totalSpent) || 0).toFixed(2)}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {payments.length > 0 ? (
                                <div className="space-y-3">
                                    {payments.map((payment) => (
                                        <div key={payment.id} className="flex items-center justify-between rounded-lg border p-3">
                                            <div className="space-y-0.5">
                                                <p className="text-sm font-medium">{payment.plan_title || 'Payment'}</p>
                                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                    <span>{formatDateTime(payment.freemius_created_at)}</span>
                                                    {payment.gateway && <span>&middot; {payment.gateway}</span>}
                                                    {payment.is_renewal && (
                                                        <Badge variant="outline" className="h-4 px-1 py-0 text-[10px]">
                                                            Renewal
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-semibold">
                                                    {payment.currency?.toUpperCase()} {(Number(payment.gross) || 0).toFixed(2)}
                                                </p>
                                                {payment.invoice_url && (
                                                    <a
                                                        href={payment.invoice_url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-xs text-primary hover:underline"
                                                    >
                                                        Invoice
                                                    </a>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">No payments found</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Pregnancy Tracking */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Download className="size-5" />
                                Pregnancy Tracking
                            </CardTitle>
                            <CardDescription>{activations.length} active installation(s)</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {activations.length > 0 ? (
                                <div className="space-y-3">
                                    {activations.map((activation) => (
                                        <div key={activation.id} className="flex items-start justify-between rounded-lg border bg-muted/50 p-4">
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <ShoppingCart className="size-4 text-muted-foreground" />
                                                    <p className="text-sm font-semibold">{activation.title || activation.uid}</p>
                                                </div>
                                                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
                                                    {activation.license_plan_name && <span>Plan: {activation.license_plan_name}</span>}
                                                    {activation.version && <span>v{activation.version}</span>}
                                                    <span>Activated: {formatDate(activation.activated_at)}</span>
                                                </div>
                                                {activation.url && (
                                                    <a
                                                        href={activation.url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="text-xs text-primary hover:underline"
                                                    >
                                                        {activation.url}
                                                    </a>
                                                )}
                                            </div>
                                            <Badge variant="outline" className="shrink-0">
                                                Active
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="py-8 text-center text-sm text-muted-foreground">No Pregnancy Tracking found</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
