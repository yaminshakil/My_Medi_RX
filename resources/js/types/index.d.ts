import type { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Auth {
    user: User;
    permissions?: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface NavItemWithSubmenu extends NavItem {
    href?: string;
    submenu?: NavItem[] | null;
    permission?: string;
}

export interface FlashMessage {
    success?: string;
    error?: string;
    [key: string]: unknown; // Allows safe key indexing on session object
}

export interface Flash {
    message: FlashMessage;
}

export interface SharedData {
    name: string;
    quote: {
        message: string;
        author: string;
    };
    auth: Auth;
    ziggy: Config & {
        location: string;
    };
    sidebarOpen: boolean;
    flash: Flash;
    [key: string]: unknown;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & SharedData;