import { ConfirmDialog } from '@/Components/ConfirmDialog';
import { AdminPageHeader } from '@/Components/AdminPageHeader';
import PageHeader from '@/Components/PageHeader';
import { Pagination } from '@/Components/pagination';
import Table from '@/Components/Table';
import { Button } from '@/Components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import AppLayout from '@/Layouts/app-layout';
import { useCan } from '@/lib/can';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Eye, X } from 'lucide-react';
import { type ComponentType, useEffect, useState } from 'react';
import { ToastContainer, toast } from 'react-toastify';


interface Role {
    id: number;
    name: string;
}

interface UserRow {
    id: number;
    name: string;
    email: string;
    roles: Role[];
    created_at: string;
}

interface UserFilters {
    search?: string;
    per_page?: string | number;
    role?: string;
}

interface FlashProps {
    message?: {
        success?: string;
        error?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const DataTable = Table as ComponentType<any>;

export default function Index({ users, roles = [] }: { users: { data: UserRow[] }; roles?: Role[] }) {
    const { flash, filters: pageFilters } = usePage().props as unknown as { flash: FlashProps; filters?: UserFilters };
    const filters = pageFilters ?? {};
    const [showConfirm, setShowConfirm] = useState(false);
    const [user, setUser] = useState<UserRow | null>(null);
    const canCreate = useCan('Create');
    const canEdit = useCan('Edit');
    const canDelete = useCan('Delete');

    const { data, setData } = useForm({
        search: filters?.search || '',
        role: filters?.role || 'all',
    });

    const cleanFilters = (params: Record<string, string | number | null | undefined>) =>
        Object.fromEntries(Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== ''));

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setData('search', value);
        router.get(route('users.index'), cleanFilters({ ...filters, search: value, page: undefined }), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleRoleChange = (value: string) => {
        setData('role', value);
        router.get(
            route('users.index'),
            cleanFilters({
                ...filters,
                role: value === 'all' ? undefined : value,
                page: undefined,
            }),
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleReset = () => {
        setData('search', '');
        setData('role', 'all');
        router.get(
            route('users.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    useEffect(() => {
        if (flash.message?.success) {
            toast.success(flash.message.success);
        }
        if (flash.message?.error) {
            toast.error(flash.message.error);
        }
    }, [flash]);

    const handleConfirm = () => {
        if (!user) {
            return;
        }

        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
        });
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    const handlePerPageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        router.get(route('users.index'), { ...filters, per_page: e.target.value, page: undefined }, { preserveState: true });
    };

    const handleEdit = (item: UserRow) => {
        router.get(route('users.edit', item.id));
    };

    const handleDelete = (item: UserRow) => {
        setUser(item);
        setShowConfirm(true);
    };

    const columns: any[] = [
        { key: '#', label: '#', render: (item: UserRow) => item.id },
        {
            key: 'name',
            label: 'Name',
            render: (item: UserRow) => (
                <a href={route('users.show', item.id)} className="font-medium text-primary hover:underline">
                    {item.name}
                </a>
            ),
        },
        { key: 'email', label: 'Email' },
        {
            key: 'role',
            label: 'Roles',
            className: 'text-center',
            render: (item: UserRow) =>
                item.roles.map((role: Role) => (
                    <span key={role.id || role.name} className="mr-1 bg-green-100 text-xs font-medium text-green-800">
                        {role.name}
                    </span>
                )),
        },
        { key: 'created_at', label: 'Created At' },
    ];

    const handleCreate = () => {
        router.visit(route('users.create'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <ToastContainer />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader
                    breadcrumbs={breadcrumbs}
                    description="Manage registered users and their roles"
                    toolbar={
                        <PageHeader
                            data={data}
                            perPageItem={filters.per_page}
                            handlePerPageChange={handlePerPageChange}
                            handleSearch={handleSearch}
                            placeholder="Search user ..."
                            handleReset={handleReset}
                            handleCreate={handleCreate}
                            canCreate={canCreate}
                        />
                    }
                    filters={
                        <div className="flex items-center -space-x-px">
                            <Select value={data.role} onValueChange={handleRoleChange}>
                                {/* Added w-[200px] to ensure a consistent footprint */}
                                <SelectTrigger className="h-10 w-[180px] lg:w-[240px]">
                                    <SelectValue placeholder="Filter by role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Roles</SelectItem>
                                    {roles.map((role) => (
                                        <SelectItem key={role.id || role.name} value={role.name}>
                                            {role.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            {/* Using ghost or outline variant for a cleaner look if not "dangerous" */}
                            <Button
                                variant="destructive"
                                size="icon"
                                onClick={handleReset}
                                className="h-10 w-18 shrink-0 hover:bg-red-500 hover:text-white px-3"
                                title="Reset filters"
                            >
                                <X size={10} />
                                Clear
                            </Button>
                        </div>
                    }
                />
                <div className="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <div className="inline-block min-w-full">
                        <DataTable
                            items={users.data}
                            columns={columns}
                            canEdit={canEdit}
                            canDelete={canDelete}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            renderActions={(item: UserRow) => (
                                <Button variant="outline" size="sm" onClick={() => router.visit(route('users.show', item.id))}>
                                    <Eye className="mr-1 h-4 w-4" /> View
                                </Button>
                            )}
                            actionHeadClass="border p-4 w-32 text-center"
                            emptyMessage="No User found."
                        />
                        <div className="mt-4">
                            <Pagination items={users} />
                        </div>
                    </div>
                </div>

                <ConfirmDialog
                    title="Delete User"
                    message="Are you sure you want to delete this user?"
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                    isOpen={showConfirm}
                />
            </div>
        </AppLayout>
    );
}
