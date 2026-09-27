import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { Star } from 'lucide-react';
import { useState } from 'react';

export default function ReviewForm({ reviewableType, reviewableId }) {
    const { data, setData, post, processing, reset } = useForm({
        reviewable_type: reviewableType,
        reviewable_id: reviewableId,
        rating: 5,
        comment: '',
    });

    const [hover, setHover] = useState(0);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('reviews.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <form onSubmit={handleSubmit} className="mt-6 space-y-4 rounded-lg bg-white p-6 shadow-md">
            <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        size={32}
                        className={`cursor-pointer ${star <= (hover || data.rating) ? 'fill-[var(--base-color)] text-[var(--base-color)]' : 'text-gray-300'
                            }`}
                        onClick={() => setData('rating', star)}
                        onMouseEnter={() => setHover(star)}
                        onMouseLeave={() => setHover(0)}
                    />
                ))}
            </div>

            <Textarea
                placeholder="Write your comment..."
                value={data.comment}
                onChange={(e) => setData('comment', e.target.value)}
                className="w-full"
            />

            <Button type="submit" disabled={processing}>
                Submit
            </Button>
        </form>
    );
}
