// resources/js/Pages/Admin/Reviews/Form.tsx (shared)
import { useForm } from '@inertiajs/react';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import FormFooter from '@/Components/Form/FormFooter';
import { Star } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    users: User[];
    review?: any; // for edit
}

export default function ReviewForm({ users, review }: Props) {
    const { data, setData, post, put, processing, errors } = useForm({
        reviewable_type: review?.reviewable_type || 'App\\Models\\User',
        reviewable_id: review?.reviewable_id || '',
        user_id: review?.user_id || '',
        rating: review?.rating || 5,
        comment: review?.comment || '',
        approved: review?.approved ?? true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (review) {
            put(route('admin.reviews.update', review.id));
        } else {
            post(route('admin.reviews.store'));
        }
    };

    const handleCancel = () => {
        window.history.back();
    };

    const submitTitle = review ? 'Update Review' : 'Create Review';

    return (
        <form onSubmit={submit} className="space-y-6 max-w-2xl px-4">
            <Card>
                <CardHeader>
                    <CardTitle>{review ? 'Edit Review' : 'Create Review'}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    {/* Reviewable Type */}
                    <div className="space-y-2">
                        <Label>Reviewable Type</Label>
                        <Select
                            value={data.reviewable_type}
                            onValueChange={(v) => { setData('reviewable_type', v); console.log(v) }}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="App\\Models\\User">User</SelectItem>
                                <SelectItem value="App\\Models\\Product">Product</SelectItem>
                                <SelectItem value="App\\Models\\Post">Post</SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.reviewable_type && <p className="text-sm text-destructive">{errors.reviewable_type}</p>}
                    </div>

                    {/* Reviewable ID */}
                    <div className="space-y-2">
                        <Label>Reviewable ID</Label>
                        <Input
                            type="number"
                            value={data.reviewable_id}
                            onChange={(e) => setData('reviewable_id', e.target.value)}
                        />
                        {errors.reviewable_id && <p className="text-sm text-destructive">{errors.reviewable_id}</p>}
                    </div>

                    {/* User */}
                    <div className="space-y-2">
                        <Label>User (who wrote the review)</Label>
                        <Select
                            value={String(data.user_id)}
                            onValueChange={(v) => setData('user_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select user" />
                            </SelectTrigger>
                            <SelectContent>
                                {users.map((u) => (
                                    <SelectItem key={u.id} value={String(u.id)}>
                                        {u.name} ({u.email})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.user_id && <p className="text-sm text-destructive">{errors.user_id}</p>}
                    </div>

                    {/* Rating */}
                    <div className="space-y-2">
                        <Label>Rating (1-5)</Label>
                        <Select
                            value={String(data.rating)}
                            onValueChange={(v) => setData('rating', Number(v))}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {[1, 2, 3, 4, 5].map((r) => (
                                    <SelectItem key={r} value={String(r)}>{r}
                                        <Star
                                            key={r}
                                            size={16}
                                            className='fill-[var(--base-color)] text-[var(--base-color)]'
                                        /></SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.rating && <p className="text-sm text-destructive">{errors.rating}</p>}
                    </div>

                    {/* Comment */}
                    <div className="space-y-2">
                        <Label>Comment</Label>
                        <Textarea
                            value={data.comment}
                            onChange={(e) => setData('comment', e.target.value)}
                            rows={4}
                        />
                        {errors.comment && <p className="text-sm text-destructive">{errors.comment}</p>}
                    </div>

                    {/* Approved */}
                    <div className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="approved"
                            checked={data.approved}
                            onChange={(e) => setData('approved', e.target.checked)}
                            className="h-4 w-4"
                        />
                        <Label htmlFor="approved">Approved</Label>
                    </div>
                </CardContent>
            </Card>
            <FormFooter handleCancel={handleCancel} processing={processing} submitTitle={submitTitle} />
        </form>
    );
}
