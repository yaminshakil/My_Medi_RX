import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { GripVertical } from "lucide-react";

export default function SortableItem({ id, children }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id });
    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div ref={setNodeRef} style={style} className="flex items-center">
            <div
                {...attributes}
                {...listeners}
                className="cursor-grab p-2 text-gray-400 hover:text-gray-600"
            >
                <GripVertical size={16} />
            </div>
            <div className="flex-1">{children}</div>
        </div>
    );
}
