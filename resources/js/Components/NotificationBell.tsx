import { Bell } from 'lucide-react';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import echo from '@/echo';

interface NotificationItem {
    id: string;
    title: string;
    message: string;
    url: string;
    created_at: string;
}

interface NotificationProps {
    auth: {
        user: {
            id: number;
        };
    };

    notifications: {
        unread_count: number;
        items: NotificationItem[];
    };
}

export default function NotificationBell() {
    const { auth, notifications } =
        usePage<NotificationProps>().props;

    const [isOpen, setIsOpen] = useState(false);

    const [items, setItems] = useState(
        notifications.items
    );

    const [unreadCount, setUnreadCount] = useState(
        notifications.unread_count
    );

    const notificationRef =
        useRef<HTMLDivElement>(null);

    /*
     * Listen for real-time notifications
     */
    useEffect(() => {
        const channelName = `App.Models.User.${auth.user.id}`;

        console.log(
            '🔔 Subscribing to:',
            JSON.stringify(channelName)
        );

        const channel = echo.private(channelName);

        channel.notification(
            (notification: NotificationItem) => {
                console.log(
                    '🔥 New real-time notification:',
                    notification
                );

                setItems((current) => [
                    {
                        ...notification,
                        id:
                            notification.id ??
                            crypto.randomUUID(),
                    },
                    ...current,
                ]);

                setUnreadCount((count) => count + 1);
            }
        );

        return () => {
            console.log(
                '🔕 Leaving:',
                JSON.stringify(channelName)
            );

            echo.leave(channelName);
        };
    }, [auth.user.id]);

    /*
     * Close dropdown when clicking outside
     */
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                notificationRef.current &&
                !notificationRef.current.contains(
                    event.target as Node
                )
            ) {
                setIsOpen(false);
            }
        };

        document.addEventListener(
            'mousedown',
            handleClickOutside
        );

        return () => {
            document.removeEventListener(
                'mousedown',
                handleClickOutside
            );
        };
    }, []);

    /*
     * Handle notification click
     */
    const handleNotificationClick = (
        id: string,
        url: string
    ) => {
        router.post(
            route('notifications.read', id),
            {},
            {
                onSuccess: () => {
                    setUnreadCount((count) =>
                        Math.max(count - 1, 0)
                    );

                    setItems((current) =>
                        current.filter(
                            (item) => item.id !== id
                        )
                    );

                    setIsOpen(false);

                    router.visit(url);
                },
            }
        );
    };

    return (
        <div
            ref={notificationRef}
            className="relative"
        >
            {/* Bell */}
            <button
                type="button"
                onClick={() =>
                    setIsOpen((previous) => !previous)
                }
                className="relative rounded-full p-2 hover:bg-gray-100"
                aria-label="Notifications"
                aria-expanded={isOpen}
            >
                <Bell size={22} />

                {unreadCount > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs text-white">
                        {unreadCount}
                    </span>
                )}
            </button>

            {/* Dropdown */}
            {isOpen && (
                <div className="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-lg border bg-white shadow-lg">
                    <div className="border-b p-3 font-semibold">
                        Notifications
                    </div>

                    {items.length === 0 ? (
                        <div className="p-5 text-center text-sm text-gray-500">
                            No new notifications
                        </div>
                    ) : (
                        items.map((notification) => (
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
