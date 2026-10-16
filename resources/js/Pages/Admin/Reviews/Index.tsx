import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { MoreHorizontal, Plus, Search } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/Layouts/app-layout';

interface Review {
    id: number;
    rating: number;
    comment: string | null;
    approved: boolean;
    datetime: string;
    user: { id: number; name: string };
    reviewable_type: string;
    reviewable_id: number;
    reviewable?: { id: number; name?: string; title?: string };
}

interface Props {
    reviews: {
        data: Review[];
        links: any;
        meta: any;
    };
    filters: { search?: string; approved?: string };
}

export default function Index({ reviews, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('admin.reviews.index'), { search }, { preserveState: true });
    };

    const deleteReview = (id: number) => {
        if (confirm('Are you sure you want to delete this review?')) {
            router.delete(route('admin.reviews.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title="Manage Reviews" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Reviews</h1>
                        <p className="text-muted-foreground">Manage all user reviews</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.reviews.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Review
                        </Link>
                    </Button>
                </div>

                {/* Filters */}
                <form onSubmit={handleSearch} className="flex gap-3">
                    <div className="relative flex-1 max-w-sm">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            placeholder="Search by comment or user..."
                            className="pl-8"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                    <Button type="submit" variant="secondary">Search</Button>
                </form>

                {/* Table */}
                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>User</TableHead>
                                <TableHead>Reviewable</TableHead>
                                <TableHead>Rating</TableHead>
                                <TableHead>Comment</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead className="w-[70px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {reviews.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="h-24 text-center text-muted-foreground">
                                        No reviews found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                reviews.data.map((review) => (
                                    <TableRow key={review.id}>
                                        <TableCell className="font-medium">{review.user.name}</TableCell>
                                        <TableCell>
                                            <div className="text-sm">
                                                {review.reviewable_type.split('\\').pop()}
                                                <span className="text-muted-foreground ml-1">#{review.reviewable_id}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1 ">
                                                {'★'.repeat(review.rating)}
                                                <span className="text-muted-foreground text-xs ">({review.rating})</span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="max-w-xs truncate">
                                            {review.comment || <span className="text-muted-foreground">—</span>}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={review.approved ? 'default' : 'secondary'}
                                                className={review.approved ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-500 hover:bg-yellow-600 text-black'}>
                                                {review.approved ? 'Approved' : 'Pending'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-muted-foreground text-sm">
                                            {review.datetime}
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={route('admin.reviews.edit', review.id)}>Edit</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => router.patch(route('admin.reviews.toggle-approved', review.id))}
                                                    >
                                                        {review.approved ? 'Unapprove' : 'Approve'}
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        className="text-destructive"
                                                        onClick={() => deleteReview(review.id)}
                                                    >
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {/* Simple pagination */}
                <div className="flex justify-end gap-2">
                    {reviews.links.map((link: any, i: number) => (
                        <Button
                            key={i}
                            variant={link.active ? 'default' : 'outline'}
                            size="sm"
                            disabled={!link.url}
                            onClick={() => link.url && router.get(link.url)}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
