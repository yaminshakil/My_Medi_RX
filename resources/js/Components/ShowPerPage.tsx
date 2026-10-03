import { usePage } from '@inertiajs/react';
import { AppPageProps } from '@/types';

export default function ShowPerPage({ perPageItem, handlePerPageChange }) {
    const { appSettings } = usePage().props;
    const { translations } = usePage<AppPageProps>().props;
    return (
        <div>
            <label className="mr-2 text-sm text-gray-600">{translations.messages.show}</label>
            <select
                value={perPageItem ? perPageItem : appSettings?.records_per_page}
                onChange={(e) => {
                    handlePerPageChange(e);
                }}
                className="rounded border border-gray-300 py-2 pr-8 pl-3"
            >
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span className="ml-2 text-sm text-gray-600">{translations.messages.rows_per_page}</span>
        </div>
    );
}
