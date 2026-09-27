import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/Components/ui/sidebar';
import { NavItemWithSubmenu } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import * as LucideIcons from 'lucide-react';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';

export function NavMain({ items = [] }: { items: NavItemWithSubmenu[] }) {
    const page = usePage();
    const [hover, setHover] = useState(false);
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="text-white">Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => {
                    const Icon = LucideIcons[item.icon] || LucideIcons.Circle; // fallback
                    return (
                        <Collapsible
                            key={item.title}
                            defaultOpen={item.submenu?.some((subitem) => subitem.href == page.url)}
                            className="group/collapsible"
                        >
                            <SidebarMenuItem>
                                <CollapsibleTrigger asChild>
                                    <SidebarMenuButton asChild isActive={page.url.startsWith(item.href)} tooltip={{ children: item.title }}>
                                        {item.href ? (
                                            <Link href={item.href} preserveState={false} preserveScroll={true} className="text-white">
                                                {<Icon />}
                                                <span>{item.title}</span>
                                            </Link>
                                        ) : (
                                            <div className="cursor-pointer text-white">
                                                {<Icon />}
                                                <span>{item.title}</span>
                                                <ChevronDown className="ml-auto text-white transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                            </div>
                                        )}
                                    </SidebarMenuButton>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    {item.submenu && (
                                        <SidebarMenuSub>
                                            {item.submenu.map((subitem) => {
                                                const SubIcon = LucideIcons[subitem.icon] || LucideIcons.Circle; // fallback
                                                const isActive = page.url === subitem.href;
                                                const iconColor = isActive ? '#000000' : hover ? '#000000' : '#ffffff';
                                                return (
                                                    <SidebarMenuSubItem key={subitem.title}>
                                                        <SidebarMenuSubButton asChild isActive={isActive}>
                                                            <Link
                                                                href={subitem.href}
                                                                preserveState={false}
                                                                preserveScroll={true}
                                                                className="text-white"
                                                                onMouseEnter={() => setHover(true)}
                                                                onMouseLeave={() => setHover(false)}
                                                            >
                                                                {<SubIcon color={iconColor} />}
                                                                <span>{subitem.title}</span>
                                                            </Link>
                                                        </SidebarMenuSubButton>
                                                    </SidebarMenuSubItem>
                                                );
                                            })}
                                        </SidebarMenuSub>
                                    )}
                                </CollapsibleContent>
                            </SidebarMenuItem>
                        </Collapsible>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
