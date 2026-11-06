import { Star, StarHalf } from "lucide-react";

export default function RenderStars({ rating }) {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;

    // Full stars
    for (let i = 0; i < fullStars; i++) {
        stars.push(
            <Star
                key={`full-${i}`}
                size={18}
                className="fill-[var(--base-color)] text-[var(--base-color)]"
            />,
        );
    }

    // Half star
    if (hasHalfStar) {
        stars.push(
            <StarHalf
                key="half"
                size={18}
                className="fill-[var(--base-color)] text-[var(--base-color)]"
            />,
        );
    }

    // Empty stars
    const emptyStars = 5 - Math.ceil(rating);
    for (let i = 0; i < emptyStars; i++) {
        stars.push(
            <Star key={`empty-${i}`} size={18} className="text-gray-300" />,
        );
    }

    return stars;
}
