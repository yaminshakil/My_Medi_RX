import Navbar from '@/Components/Navbar';
import FooterFrontend from '@/Layouts/FooterFrontend';

export default function Guest({ children }) {
    return (
        <div className="mx-auto my-0">
            <div className="flex min-h-[100dvh] flex-col items-center bg-white pt-6 sm:justify-center sm:pt-0">
                <main className="w-full flex-1">
                    <Navbar />
                    <div className="container mx-auto">{children}</div>
                </main>
            </div>
            <FooterFrontend />
        </div>
    );
}
