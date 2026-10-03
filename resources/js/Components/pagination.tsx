import { Link, usePage } from '@inertiajs/react';
import { AppPageProps } from '@/types';

export const Pagination = ({ items }) => {
    const { translations } = usePage<AppPageProps>().props;
    return (
        <div className="item-center flex justify-between">
            <p>
                {' '}
                {translations.common.Showing} <strong>{items.from} </strong> {translations.common.to} <strong>{items.to}</strong> {translations.common.from_total} <strong> {items.total}</strong> {translations.common.entries}
            </p>
            <div className="flex gap-1">
                {items.links.map((link, index) => (
                    <Link
                        className={`rounded border px-3 py-1 ${link.active ? 'bg-[var(--btn-base-color)] text-white hover:bg-[var(--btn-base-hover-color)]' : ''}`}
                        href={link.url || '#'}
                        key={index}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </div>
    );
};
