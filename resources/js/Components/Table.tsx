import { Button } from '@/Components/ui/button';
import useFormatCurrency from '@/lib/utils';
import { router } from '@inertiajs/react';
import { Edit, Trash2 } from 'lucide-react';

/**
 * Generic TableBody component
 * @param {Array} items - Array of data objects to render
 * @param {Array} columns - Array of column configs: [{ key: 'name', label: 'Name', render?: (item) => any }]
 * @param {Boolean} canEdit - Whether to show edit button
 * @param {Boolean} canDelete - Whether to show delete button
 * @param {Function} onEdit - Handler for edit button (item)
 * @param {Function} onDelete - Handler for delete button (item)
 * @param {String} emptyMessage - Message to show when no data
 */
export default function Table({
    items = [],
    columns = [],
    canEdit = false,
    canDelete = false,
    onEdit,
    onDelete,
    renderActions,
    actionHeadClass = 'border p-4 text-center',
    actionLabel = 'Actions',
    emptyMessage = 'No records found.',
}) {
    const formatCurrency = useFormatCurrency();
    return (
        <table className="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
            <thead className="bg-[var(--base-color)] text-xs text-white uppercase dark:bg-gray-700 dark:text-gray-400">
                <tr className="bg-[var(--base-color)]">
                    {columns.map((col) => (
                        <th key={col.key} className={`border p-4 ${col.className || 'text-left'}`}>
                            {col.label}
                        </th>
                    ))}
                    {(canEdit || canDelete) && <th className={actionHeadClass}>{actionLabel}</th>}
                </tr>
            </thead>
            <tbody>
                {items.length > 0 ? (
                    items.map((item, index) => (
                        <tr key={item.id || index} className="hover:bg-gray-50">
                            {columns.map((col) => (
                                <td key={col.key} className={`border p-4 ${col.className || ''}`}>
                                    {col.render
                                        ? col.render(item)
                                        : col.isCurrency && typeof item[col.key] === 'number'
                                            ? formatCurrency(item[col.key])
                                            : (item[col.key] ?? '—')}
                                </td>
                            ))}

                            {(canEdit || canDelete) && (
                                <td className="border p-4 text-center align-middle">
                                    <div className="flex items-center justify-center gap-2">
                                        {canEdit && (
                                            <Button type="button" onClick={() => (onEdit ? onEdit(item) : router.get(route('edit', item.id)))}>
                                                <Edit className="mr-1 h-4 w-4" /> Edit
                                            </Button>
                                        )}
                                        {canDelete && (
                                            <Button
                                                variant="destructive"
                                                type="button"
                                                className="cursor-pointer text-white md:ml-2"
                                                onClick={() => onDelete && onDelete(item)}
                                            >
                                                <Trash2 className="mr-1 h-4 w-4" /> Delete
                                            </Button>
                                        )}
                                        {renderActions && renderActions(item)} {/* 👈 custom dropdown etc */}
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))
                ) : (
                    <tr>
                        <td colSpan={columns.length + (canEdit || canDelete ? 1 : 0)} className="p-4 text-center text-gray-500">
                            {emptyMessage}
                        </td>
                    </tr>
                )}
            </tbody>
        </table>
    );
}
