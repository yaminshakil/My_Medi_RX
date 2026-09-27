import { Button } from '@/Components/ui/button';
import { Facebook, Linkedin, MessageCircle, Twitter } from 'lucide-react';

export default function SocialShare({ url, title }) {
    const openPopup = (shareUrl) => {
        const w = 600,
            h = 500;
        const left = window.screenX + (window.outerWidth - w) / 2;
        const top = window.screenY + (window.outerHeight - h) / 2;
        window.open(shareUrl, 'share', `toolbar=0,status=0,width=${w},height=${h},top=${top},left=${left}`);
    };

    const handleFacebook = () => {
        openPopup(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`);
    };

    const handleTwitter = () => {
        openPopup(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`);
    };

    const handleLinkedIn = () => {
        openPopup(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`);
    };

    const handleWhatsApp = () => {
        window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank', 'noopener');
    };

    return (
        <div className="mt-4 flex items-center gap-2">
            <p className="font-semibold">Share On :</p>
            <Button onClick={handleFacebook} className="flex items-center gap-1 rounded p-2 text-white">
                <Facebook size={16} />
            </Button>
            <Button onClick={handleTwitter} className="flex items-center gap-1 rounded p-2 text-white">
                <Twitter size={16} />
            </Button>
            <Button onClick={handleLinkedIn} className="flex items-center gap-1 rounded p-2 text-white">
                <Linkedin size={16} />
            </Button>
            <Button onClick={handleWhatsApp} className="flex items-center gap-1 rounded p-2 text-white">
                <MessageCircle size={16} />
            </Button>
        </div>
    );
}
