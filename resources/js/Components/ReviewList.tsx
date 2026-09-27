import RenderStars from '@/Components/RenderStars';
import { Star } from 'lucide-react';

export default function ReviewList({ reviews, item }) {
    return (
        <div className="rounded-lg bg-white p-6 shadow-md">
            <h3 className="mb-4 text-xl font-semibold text-gray-800">Average Reviews {item.average_rating}</h3>
            <div className="mb-6 flex items-center">
                <div className="mr-6 text-center">
                    <div className="text-4xl font-bold text-gray-800">{item.average_rating}</div>
                    <div className="mt-1 flex justify-center">
                        <RenderStars rating={item.average_rating} />
                    </div>
                    <div className="mt-1 text-sm text-gray-600">{item.review_count} reviews</div>
                </div>
                <div className="flex-1">
                    {[5, 4, 3, 2, 1].map((star) => {
                        const percentage = item.rating_distribution?.[star] ?? 0;
                        return (
                            <div key={star} className="mb-1 flex items-center">
                                <div className="w-10 text-sm text-gray-600">{star} star</div>
                                <div className="mx-2 h-2 flex-1 rounded-full bg-gray-200">
                                    <div
                                        className="h-2 rounded-full bg-[var(--base-color)] transition-all duration-300"
                                        style={{ width: `${percentage}%` }}
                                    ></div>
                                </div>
                                <div className="w-10 text-sm text-gray-600">{percentage}%</div>
                            </div>
                        );
                    })}
                </div>
            </div>
            <div className="mt-4 space-y-4">
                {reviews &&
                    reviews.map((review) => (
                        <div key={review.id} className="border-b pb-3">
                            <div className="flex items-center gap-2">
                                <img
                                    src={review.user.profile_image_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(review.user.name)}`}
                                    alt={review.user.name}
                                    className="h-8 w-8 rounded-full"
                                />
                                <span className="font-semibold">{review.user.name}</span>
                                <p className="text-sm text-gray-500">{review.datetime}</p>
                            </div>

                            <div className="mt-1 flex gap-1">
                                {[...Array(5)].map((_, i) => (
                                    <Star
                                        key={i}
                                        size={16}
                                        className={i < review.rating ? 'fill-[var(--base-color)] text-[var(--base-color)]' : 'text-gray-300'}
                                    />
                                ))}
                            </div>

                            <p className="mt-2 text-gray-700">{review.comment}</p>
                        </div>
                    ))}
            </div>
        </div>
    );
}
