import { Bell } from 'lucide-react';
import { router, usePage } from '@inertiajs/react';
import { useState, useRef, useEffect } from 'react';

interface NotificationItem {
    id: string;
    title: string;
    message: string;
    url: string;
    created_at: string;
}

interface NotificationProps {
    notifications: {
        unread_count: number;
        items: NotificationItem[];
    };
}

export default function NotificationBell() {
    const { notifications } = usePage<NotificationProps>().props;

    const [isOpen, setIsOpen] = useState(false);

    const notificationRef = useRef<HTMLDivElement>(null);

    const handleNotificationClick = (id: string, url: string) => {
        router.post(
            route('notifications.read', id),
            {},
            {
                onSuccess: () => {
                    setIsOpen(false);
                    router.visit(url);
                },
            }
        );
    };

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                notificationRef.current &&
                !notificationRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    return (
        <div
            ref={notificationRef}
            className="relative"
        >
            {/* Notification Bell */}
            <button
                type="button"
                onClick={() => setIsOpen((prev) => !prev)}
                className="relative rounded-full p-2 hover:bg-gray-100"
                aria-label="Notifications"
                aria-expanded={isOpen}
            >
                <Bell size={22} />

                {notifications.unread_count > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs text-white">
                        {notifications.unread_count}
                    </span>
                )}
            </button>

            {/* Notification Dropdown */}
            {isOpen && (
                <div className="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-lg border bg-white shadow-lg">
                    {/* Header */}
                    <div className="border-b p-3 font-semibold">
                        Notifications
                    </div>

                    {/* Notifications */}
                    {notifications.items.length === 0 ? (
                        <div className="p-5 text-center text-sm text-gray-500">
                            No new notifications
                        </div>
                    ) : (
                        notifications.items.map((notification) => (
                            <button
                                type="button"
                                key={notification.id}
                                onClick={() =>
                                    handleNotificationClick(
                                        notification.id,
                                        notification.url
                                    )
                                }
                                className="block w-full border-b p-3 text-left hover:bg-gray-50"
                            >
                                <p className="text-sm font-semibold">
                                    {notification.title}
                                </p>

                                <p className="text-xs text-gray-500">
                                    {notification.message}
                                </p>

                                <p className="mt-1 text-xs text-gray-400">
                                    {notification.created_at}
                                </p>
                            </button>
                        ))
                    )}
                </div>
            )}
        </div>
    );
}
