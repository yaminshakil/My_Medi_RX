import ScrollToTop from '@/Components/ScrollToTop';
import { Link, usePage } from '@inertiajs/react';
import * as LucideIcons from 'lucide-react';

export default function FooterFrontend() {
    const { footer, appSettings } = usePage().props;

    return (
        <footer className="bg-[var(--footer-bgcolor)] text-white antialiased dark:bg-gray-800">
            {/* Top Section */}
            <div className="container mx-auto grid grid-cols-1 gap-8 px-4 py-14 sm:grid-cols-2 lg:grid-cols-4">
                {footer.map((section) => {
                    const listLayout = section.is_icon
                        ? 'flex items-center space-x-4' // horizontal inline for icons
                        : 'space-y-2'; // vertical for text links

                    return (
                        <div key={section.id}>
                            {section.is_content ? (
                                <div className="space-y-2">
                                    <Link className="flex" href="/">
                                        <img src={`/storage/${section.footer_contents.logo}`} alt="Current Logo" className="h-16" />
                                    </Link>
                                    <div className="flex font-medium">{section.footer_contents.content}</div>
                                </div>
                            ) : (
                                <div>
                                    <h4 className="mb-3 inline-block border-b-3 border-[var(--base-color)] pb-1 text-lg font-semibold">
                                        {section.title}
                                    </h4>
                                    <ul className={listLayout}>
                                        {section.links.map((link) => {
                                            let Icon = null;
                                            if (link.is_icon) {
                                                Icon = LucideIcons[link.label] || LucideIcons.Circle;
                                            }

                                            const content = link.is_icon ? <Icon className="h-5 w-5" /> : link.label;
                                            const iconclassName = link.is_icon
                                                ? 'flex items-center gap-1 text-white hover:text-gray-300'
                                                : 'flex items-center justify-between gap-2 border-b border-white/20 py-1 text-white hover:text-gray-300 transition-colors';

                                            return (
                                                <li key={link.id}>
                                                    {link.is_external ? (
                                                        <a href={link.url} target="_blank" rel="noopener noreferrer" className={iconclassName}>
                                                            {content}
                                                            {link.is_icon ? null : (
                                                                <LucideIcons.ChevronRight className="h-4 w-4 text-white/70 transition-colors group-hover:text-white" />
                                                            )}
                                                        </a>
                                                    ) : (
                                                        <Link
                                                            href={link.url}
                                                            className="flex items-center justify-between gap-2 border-b border-white/20 py-1 text-white transition-colors hover:text-gray-300"
                                                        >
                                                            {content}
                                                            <LucideIcons.ChevronRight className="h-4 w-4 text-white/70 transition-colors group-hover:text-white" />
                                                        </Link>
                                                    )}
                                                </li>
                                            );
                                        })}
                                    </ul>
                                </div>
                            )}
                            {section.is_icon == true && (
                                <div className="mt-4 space-y-2">
                                    {appSettings.site_mobile && (
                                        <p className="flex">
                                            <LucideIcons.Phone className="mr-1 h-5 w-5" />
                                            {appSettings.site_country_code}-{appSettings.site_mobile}
                                        </p>
                                    )}
                                    {appSettings.site_email && (
                                        <p className="flex">
                                            <LucideIcons.Mail className="mr-1 h-5 w-5" />
                                            {appSettings.site_email}
                                        </p>
                                    )}
                                    {appSettings.site_address && (
                                        <p className="flex">
                                            <LucideIcons.MapPin className="mr-1 h-5 w-5" />
                                            {appSettings.site_address}
                                        </p>
                                    )}
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>

            {/* Bottom Section */}
            <div className="w-full bg-[var(--footerbottom-bgcolor)] py-4 text-center dark:bg-gray-600">
                <p className="text-sm text-gray-200 dark:text-gray-400">
                    &copy; {new Date().getFullYear()}
                    <a href="#" className="ml-1 text-[var(--base-color)] hover:underline" target="_blank">
                        {appSettings.site_title}
                    </a>
                    . All rights reserved.
                </p>
            </div>
            <ScrollToTop behavior="smooth" />
        </footer>
    );
}
