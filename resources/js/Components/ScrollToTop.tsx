import { Button } from '@/Components/ui/button';
import { ArrowUp } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function ScrollToTop({ behavior = 'auto' }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            // Show button when user scrolls 300px down
            setVisible(window.scrollY > 300);
        };

        window.addEventListener('scroll', toggleVisibility);
        return () => window.removeEventListener('scroll', toggleVisibility);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: behavior });
    };

    return (
        <div className={`fixed right-6 bottom-6 transition-opacity duration-300 ${visible ? 'opacity-100' : 'pointer-events-none opacity-0'}`}>
            <Button size="icon" onClick={scrollToTop} className="cursor-pointer rounded-full shadow-lg hover:shadow-xl">
                <ArrowUp className="h-5 w-5" />
            </Button>
        </div>
    );
}
