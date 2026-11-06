import { useEffect, useRef, useState } from "react";

// Reuse the efficient visibility hook (useOnScreen)
const useOnScreen = (ref, rootMargin = "0px") => {
    const [isIntersecting, setIntersecting] = useState(false);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                // Set state to true when the element enters the viewport
                setIntersecting(entry.isIntersecting);
            },
            { rootMargin },
        );

        if (ref.current) {
            observer.observe(ref.current);
        }

        return () => {
            if (ref.current) {
                observer.unobserve(ref.current);
            }
        };
    }, [ref, rootMargin]);

    return isIntersecting;
};

const AnimatedHeading = ({
    children,
    // Accept the extra class from the parent
    className: extraClass = "",
}) => {
    const headingRef = useRef(null);
    const isVisible = useOnScreen(headingRef, "-100px");

    // 1. Split the children text into an array of words
    const words = children.split(" ");

    // Base class for the main wrapper (the <h2>)
    const baseClass = `animated-heading-wrapper ${extraClass}`;

    return (
        <h2 ref={headingRef} className={baseClass}>
            {words.map((word, index) => (
                <span
                    key={index}
                    className={`animated-word ${isVisible ? "word-visible" : ""}`}
                    style={{
                        transitionDelay: isVisible ? `${index * 0.1}s` : "0s",
                    }}
                >
                    {word}
                    {/* REMOVED: {index < words.length - 1 ? ' ' : ''} */}
                </span>
            ))}
        </h2>
    );
};

export default AnimatedHeading;
