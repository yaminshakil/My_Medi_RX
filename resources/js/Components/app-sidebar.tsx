import { NavFooter } from '@/Components/nav-footer';
import { NavMain } from '@/Components/nav-main';
import { NavUser } from '@/Components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/Components/ui/sidebar';
import { NavItemWithSubmenu, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { props } = usePage();
    const menus = props.menus || {};
    const mainNavItems: NavItemWithSubmenu[] = menus;
    const { appSettings } = usePage().props as { appSettings: Record<string, string> };
    const logoUrl = appSettings?.dashboard_logo ? `/storage/${appSettings?.dashboard_logo}` : '/default/dashboard_logo.png';

    return (
        <Sidebar collapsible="icon" variant="inset" className="bg-[var(--base-color)] dark:bg-gray-800">
            <SidebarHeader className="bg-[var(--base-color)] dark:bg-gray-800">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <Link href={route('dashboard')} prefetch>
                            <img src={logoUrl} alt="Logo" className="mx-auto my-4 h-auto max-w-[100%]" />
                        </Link>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="bg-[var(--base-color)] dark:bg-gray-800">
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter className="bg-[var(--base-color)] dark:bg-gray-800">
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
