import { ConfirmDialog } from '@/Components/ConfirmDialog';
import { Button } from '@/Components/ui/button';
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { router } from '@inertiajs/react';
import { Edit, GripVertical, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function SortableItem({ id, menu, onDelete }) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id,
        data: {
            type: 'menu',
            menu: menu,
        },
    });

    const [showConfirm, setShowConfirm] = useState(false);

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const handleEdit = () => {
        router.get(route('menus.edit', menu.id));
    };

    const handleDelete = () => {
        onDelete(menu.id); // ✅ call parent handler
        setShowConfirm(false);
    };

    const handleCancel = () => {
        setShowConfirm(false);
    };

    return (
        <li
            ref={setNodeRef}
            style={style}
            {...attributes} // keep attributes for accessibility
            className={`rounded border bg-white p-2 shadow ${isDragging ? 'border-blue-500' : 'border-gray-200'}`}
        >
            <div className="flex items-center justify-between">
                {/* Left side: drag handle + name */}
                <div className="flex items-center space-x-2">
                    <span
                        {...listeners} // ✅ only here = drag handle
                        className="cursor-grab rounded p-1 hover:bg-gray-100"
                    >
                        <GripVertical className="h-4 w-4 text-gray-500" />
                    </span>
                    <span>{menu.name}</span>
                </div>

                {/* Right side: actions */}
                <div className="flex space-x-2">
                    <Button type="button" className="text-white" onClick={handleEdit}>
                        <Edit className="h-4 w-4 text-white" />
                        Edit
                    </Button>
                    <Button variant="destructive" type="button" className="text-white" onClick={() => setShowConfirm(true)}>
                        <Trash2 className="h-4 w-4 text-white" />
                        Delete
                    </Button>
                </div>
            </div>

            {/* Submenus */}
            {menu.submenu && menu.submenu.length > 0 && (
                <SortableContext items={menu.submenu.map((c) => c.id)} strategy={verticalListSortingStrategy}>
                    <ul className="mt-4 ml-6 space-y-1">
                        {menu.submenu.map((child) => (
                            <SortableItem key={child.id} id={child.id} menu={child} onDelete={onDelete} />
                        ))}
                    </ul>
                </SortableContext>
            )}

            {/* Confirm Delete Modal */}
            <ConfirmDialog
                title="Delete Menu"
                message={`Are you sure you want to delete this <b>${menu.name}</b>?`}
                onConfirm={handleDelete}
                onCancel={handleCancel}
                isOpen={showConfirm}
                onClose={() => setShowConfirm(false)}
            />
        </li>
    );
}
