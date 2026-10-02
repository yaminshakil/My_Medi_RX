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
    const { auth, notifications } = usePage<NotificationProps>().props;

    const [isOpen, setIsOpen] = useState(false);
    const [items, setItems] = useState<NotificationItem[]>(notifications?.items ?? []);
    const [unreadCount, setUnreadCount] = useState<number>(notifications?.unread_count ?? 0);

    const notificationRef = useRef<HTMLDivElement>(null);

    /*
     * 1. Keep local React state synchronized with updated Inertia props
     */
    useEffect(() => {
        console.log(notifications);
        if (notifications) {
            setItems(notifications.items ?? []);
            setUnreadCount(notifications.unread_count ?? 0);
        }
    }, [notifications]);

    /*
     * 2. Listen for real-time Laravel Broadcast notifications
     */


    useEffect(() => {
        // 2. Check if auth.user.id exists
        if (!auth?.user?.id) {
            console.warn('⚠️ No auth.user.id found! Echo subscription skipped.');
            return;
        }

        console.log('📡 Subscribing to channel for User ID:', auth.user.id);

        const channelName = `App.Models.User.${auth.user.id}`;
        const channel = echo.private(channelName);

        channel.notification((notification: any) => {
            console.log('🔥 Real-time notification received:', notification);
            setItems((prev) => [{
                id: notification.id,
                title: notification.title,
                message: notification.message,
                url: notification.url ?? '#',
                created_at: notification.created_at ?? new Date().toLocaleString(),
            }, ...prev]);
            setUnreadCount((c) => c + 1);
        });

        return () => {
            echo.leave(channelName);
        };
    }, [auth?.user?.id]);

    /*
     * 3. Close dropdown when clicking outside
     */
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

    /*
     * Handle clicking an item in the dropdown
     */
    const handleNotificationClick = (id: string, url: string) => {
        router.post(
            route('notifications.read', id),
            {},
            {
                onSuccess: () => {
                    setUnreadCount((count) => Math.max(count - 1, 0));
                    setItems((current) => current.filter((item) => item.id !== id));
                    setIsOpen(false);

                    if (url) {
                        router.visit(url);
                    }
                },
            }
        );
    };

    return (
        <div ref={notificationRef} className="relative">
            {/* Bell Button */}
            <button
                type="button"
                onClick={() => setIsOpen((prev) => !prev)}
                className="relative rounded-full p-2 hover:bg-gray-100 transition-colors"
                aria-label="Notifications"
                aria-expanded={isOpen}
            >
                <Bell size={22} />

                {unreadCount > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white shadow-sm">
                        {unreadCount > 99 ? '99+' : unreadCount}
                    </span>
                )}
            </button>

            {/* Dropdown Menu */}
            {isOpen && (
                <div className="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-lg border bg-white shadow-lg">
                    <div className="border-b p-3 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Notifications</span>
                        {unreadCount > 0 && (
                            <span className="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                {unreadCount} new
                            </span>
                        )}
                    </div>

                    <div className="max-h-96 overflow-y-auto">
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
                                    className="block w-full border-b p-3 text-left hover:bg-gray-50 transition-colors"
                                >
                                    <p className="text-sm font-semibold text-gray-900">
                                        {notification.title}
                                    </p>
                                    <p className="text-xs text-gray-600 mt-0.5">
                                        {notification.message}
                                    </p>
                                    <p className="mt-1 text-xs text-gray-400">
                                        {notification.created_at}
                                    </p>
                                </button>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
