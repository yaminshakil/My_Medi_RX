import { AdminPageHeader } from '@/Components/AdminPageHeader';
import { Pagination } from '@/Components/pagination';
import AppLayout from '@/Layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { closestCenter, DndContext, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { arrayMove, SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import SortableItem from './SortableItem';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Menus',
        href: '/menus',
    },
];

export default function Index({ allmenus }) {
    const [menus, setMenus] = useState(allmenus.data);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    // helper: find parent array and index (supports top-level and one nested level)
    const findParentAndIndex = (nodes, id) => {
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].id === id) return { parentId: null, index: i };
            if (nodes[i].submenu) {
                for (let j = 0; j < nodes[i].submenu.length; j++) {
                    if (nodes[i].submenu[j].id === id) return { parentId: nodes[i].id, index: j };
                }
            }
        }
        return null;
    };

    const handleDragEnd = (event) => {
        let newMenus = [];
        const { active, over } = event;
        if (!over) return;
        const activeInfo = findParentAndIndex(menus, active.id);
        const overInfo = findParentAndIndex(menus, over.id);
        if (!activeInfo || !overInfo) return;

        // same parent => reorder within same list
        if (activeInfo.parentId === overInfo.parentId) {
            if (activeInfo.parentId === null) {
                // top-level reorder
                newMenus = arrayMove(menus, activeInfo.index, overInfo.index);
                setMenus((prev) => arrayMove(prev, activeInfo.index, overInfo.index));
            } else {
                // submenu reorder
                setMenus((prev) => {
                    const copy = JSON.parse(JSON.stringify(prev));
                    const parent = copy.find((m) => m.id === activeInfo.parentId);
                    newMenus = arrayMove(parent.submenu, activeInfo.index, overInfo.index);
                    parent.submenu = arrayMove(parent.submenu, activeInfo.index, overInfo.index);
                    return copy;
                });
            }
        }

        // Save to backend
        router.post(
            route('menus.updateOrder'),
            { menus: newMenus },
            {
                onSuccess: () => console.log('Order updated'),
                onError: (errors) => console.log('Error:', errors),
            },
        );
    };

    const removeMenuById = (items, id) => {
        return items.filter((i) => i.id !== id).map((i) => ({ ...i, submenu: i.submenu ? removeMenuById(i.submenu, id) : [] }));
    };

    const handleDeleteMenu = (id) => {
        router.delete(route('menus.destroy', id), {
            onSuccess: () => setMenus((prev) => removeMenuById(prev, id)),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Menus" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl px-4">
                <AdminPageHeader breadcrumbs={breadcrumbs} description="Manage navigation menu items and order" />
                <div className="w-full overflow-x-hidden py-6">
                    <div className="mx-auto">
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg sm:px-4">
                            <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                                <SortableContext items={menus.map((m) => m.id)} strategy={verticalListSortingStrategy}>
                                    <ul className="space-y-2">
                                        {menus.map((menu) => (
                                            <SortableItem key={menu.id} id={menu.id} menu={menu} onDelete={handleDeleteMenu} />
                                        ))}
                                    </ul>
                                </SortableContext>
                            </DndContext>
                            <div className="mt-4">
                                <Pagination items={allmenus} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
