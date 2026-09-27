import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Link, usePage } from '@inertiajs/react';
import { AlignJustify, Mail, Phone } from 'lucide-react';
import React, { useState } from 'react';

export default function Navbar() {
    const { appSettings, menuPages, auth, ziggy } = usePage().props;
    const [toggle, setToggle] = useState(false);

    const showNav = () => {
        setToggle(!toggle);
    };

    // start mobile first plus facile
    return (
        <nav className="sticky top-0 right-0 left-0 z-50 flex w-full flex-col items-center bg-[var(--base-color)] dark:bg-gray-800">
            <div className="flex min-h-5 w-full flex-wrap items-center justify-center gap-5 bg-[var(--headertop-bgcolor)] p-1 md:flex-nowrap dark:bg-gray-600">
                {appSettings.site_mobile && (
                    <span className="flex items-center justify-center gap-1 text-white">
                        <Phone size={20} />
                        {appSettings.site_country_code}
                        {appSettings.site_mobile}
                    </span>
                )}
                {appSettings.site_email && (
                    <span className="flex items-center justify-center gap-1 text-white">
                        <Mail size={20} />
                        {appSettings.site_email}
                    </span>
                )}
            </div>
            <div className="grid h-18 w-full grid-cols-2 items-center p-4 md:h-auto md:grid-cols-3">
                <div className="justify-self-start">
                    <Link href="/">
                        <img
                            className="mt-0 max-w-[150px]"
                            src={
                                appSettings.navbar_logo
                                    ? `${route().t.url}/storage/${appSettings.navbar_logo}`
                                    : `${route().t.url}/default/logo_menu.png`
                            }
                        />
                    </Link>
                </div>
                <div className="flex justify-end md:hidden">
                    <button className="flex justify-end rounded ring-1 ring-white" onClick={showNav}>
                        <AlignJustify className="h-6 w-6 text-gray-700" />
                    </button>
                    {auth?.user?.id && (
                        <div className={`${toggle ? 'flex' : 'flex'} flex-row gap-2 md:flex`}>
                            <div className="flex sm:ml-6 sm:items-center">
                                <div className="relative ml-3">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <button
                                                type="button"
                                                className="flex flex-col items-center rounded-full border border-white text-sm leading-4 font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                <img
                                                    src={
                                                        auth?.user?.profile_image_url ||
                                                        `https://ui-avatars.com/api/?name=${encodeURIComponent(auth?.user?.name)}&size=128`
                                                    }
                                                    alt={`${auth?.user?.name}`}
                                                    className="h-10 w-10 rounded-full border-2 border-white shadow-lg"
                                                    loading="eager"
                                                    decoding="sync"
                                                />
                                                <svg
                                                    className="mt-[-15px] -mr-0.5 ml-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </DropdownMenuTrigger>

                                        <DropdownMenuContent className="w-56 rounded-lg bg-white shadow-md" align="end">
                                            <DropdownMenuLabel>{auth.user.name}</DropdownMenuLabel>
                                            <DropdownMenuSeparator />

                                            <DropdownMenuItem asChild>
                                                <Link href={route('user.profile')}>Profile</Link>
                                            </DropdownMenuItem>

                                            <DropdownMenuSeparator />

                                            <DropdownMenuItem asChild>
                                                <Link href={route('logout')} method="post" as="button">
                                                    Log Out
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <ul
                    className={`hidden w-full flex-col items-center justify-center first:mt-2 md:flex md:w-auto md:flex-row md:space-x-4 md:bg-transparent`}
                >
                    <li>
                        <Link className="font-semibold text-white hover:text-sky-500" href="/" onClick={showNav}>
                            Home
                        </Link>
                    </li>
                    {menuPages.map((link, index) => {
                        return link.children.length > 0 ? (
                            // If has submenu
                            <li key={index} className="group relative">
                                <Link
                                    href={route('pages.show', link.slug)}
                                    onClick={showNav}
                                    className={`font-semibold transition-colors duration-300 ${ziggy.location === route('pages.show', link.slug)
                                            ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                            : 'text-white hover:text-sky-500'
                                        }`}
                                >
                                    {link.title}
                                </Link>
                                <ul className="absolute left-0 mt-0 hidden w-48 rounded bg-[var(--btn-base-color)] shadow-lg group-hover:block">
                                    {link.children.map((sub, sidx) => (
                                        <li key={sidx} className="border-b border-white last:border-b-0">
                                            <Link
                                                href={sub.url}
                                                className={`block px-4 py-2 text-sm text-white hover:bg-[var(--btn-base-hover-color)] ${ziggy.location === sub.url
                                                        ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                                        : 'text-white decoration-2 underline-offset-4 hover:underline'
                                                    }`}
                                            >
                                                {sub.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </li>
                        ) : (
                            <li key={index} className={link.cname}>
                                <Link
                                    href={route('pages.show', link.slug)}
                                    onClick={showNav}
                                    className={`font-semibold transition-colors duration-300 ${ziggy.location === route('pages.show', link.slug)
                                            ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                            : 'text-white hover:text-sky-500'
                                        }`}
                                >
                                    {link.title}
                                </Link>
                            </li>
                        );
                    })}
                </ul>
                {auth?.user?.id ? (
                    <div className={`${toggle ? 'flex' : 'hidden'} flex-row gap-2 justify-self-end md:flex`}>
                        <div className="hidden sm:ml-6 sm:flex sm:items-center">
                            <div className="relative ml-3">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <button
                                            type="button"
                                            className="flex flex-col items-center rounded-full border border-white text-sm leading-4 font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                        >
                                            <img
                                                src={
                                                    auth?.user?.profile_image_url ||
                                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(auth?.user?.name)}&size=128`
                                                }
                                                alt={`${auth?.user?.name}`}
                                                className="h-10 w-10 rounded-full border-2 border-white shadow-lg"
                                                loading="eager"
                                                decoding="sync"
                                            />
                                            <svg
                                                className="mt-[-15px] -mr-0.5 ml-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </DropdownMenuTrigger>

                                    <DropdownMenuContent className="w-56 rounded-lg bg-white shadow-md" align="end">
                                        <DropdownMenuLabel>
                                            <Link href={route('dashboard')}>{auth.user.name}</Link>
                                        </DropdownMenuLabel>
                                        <DropdownMenuSeparator />

                                        <DropdownMenuItem asChild>
                                            <Link href={route('user.profile')} className="cursor-pointer">
                                                Profile
                                            </Link>
                                        </DropdownMenuItem>

                                        <DropdownMenuSeparator />

                                        <DropdownMenuItem asChild>
                                            <Link href={route('logout')} className="cursor-pointer" method="post" as="button">
                                                Log Out
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className={`hidden flex-row justify-end gap-2 md:flex`}>
                        <Link
                            href="/login"
                            className={`${toggle ? 'flex' : 'hidden'
                                } mx-auto mt-4 items-center justify-center rounded-lg border border-white bg-[var(--btn-base-color)] px-4 py-2 font-medium text-white hover:bg-[var(--btn-base-hover-color)] md:mx-0 md:mt-0 md:flex`}
                        >
                            Login
                        </Link>
                        <Link
                            href={route('register')}
                            className={`${toggle ? 'flex' : 'hidden'
                                } mx-auto mt-4 items-center justify-center rounded-lg border border-white bg-[var(--btn-base-color)] px-4 py-2 font-medium text-white hover:bg-[var(--btn-base-hover-color)] md:mx-0 md:mt-0 md:flex`}
                        >
                            Registration
                        </Link>
                    </div>
                )}
            </div>
            {toggle && (
                <React.Fragment>
                    <ul className={`flex w-full flex-col items-center justify-center first:mt-2 md:hidden`}>
                        <li>
                            <Link className="font-semibold text-white hover:text-sky-500" href="/" onClick={showNav}>
                                Home
                            </Link>
                        </li>
                        {menuPages.map((link, index) => {
                            return link.children.length > 0 ? (
                                // If has submenu
                                <li key={index} className="group relative">
                                    <Link
                                        href={route('pages.show', link.slug)}
                                        onClick={showNav}
                                        className={`font-semibold transition-colors duration-300 ${ziggy.location === route('pages.show', link.slug)
                                                ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                                : 'text-white hover:text-sky-500'
                                            }`}
                                    >
                                        {link.title}
                                    </Link>
                                    <ul className="absolute left-0 mt-0 hidden w-48 rounded bg-[var(--btn-base-color)] shadow-lg group-hover:block">
                                        {link.children.map((sub, sidx) => (
                                            <li key={sidx} className="border-b border-white last:border-b-0">
                                                <Link
                                                    href={sub.url}
                                                    className={`block px-4 py-2 text-sm text-white hover:bg-[var(--btn-base-hover-color)] ${ziggy.location === sub.url
                                                            ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                                            : 'text-white decoration-2 underline-offset-4 hover:underline'
                                                        }`}
                                                >
                                                    {sub.title}
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                </li>
                            ) : (
                                <li key={index} className={link.cname}>
                                    <Link
                                        href={route('pages.show', link.slug)}
                                        onClick={showNav}
                                        className={`font-semibold transition-colors duration-300 ${ziggy.location === route('pages.show', link.slug)
                                                ? 'text-sky-400 underline decoration-2 underline-offset-4'
                                                : 'text-white hover:text-sky-500'
                                            }`}
                                    >
                                        {link.title}
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                    <div className={`flex flex-row justify-end gap-2 pb-4 md:hidden`}>
                        <Link
                            href="/login"
                            className={`${toggle ? 'flex' : 'hidden'
                                } mx-auto mt-4 items-center justify-center rounded-lg border border-white bg-[var(--btn-base-color)] px-4 py-2 font-medium text-white hover:bg-[var(--btn-base-hover-color)] md:mx-0 md:mt-0 md:flex`}
                        >
                            Login
                        </Link>
                        <Link
                            href={route('register')}
                            className={`${toggle ? 'flex' : 'hidden'
                                } mx-auto mt-4 items-center justify-center rounded-lg border border-white bg-[var(--btn-base-color)] px-4 py-2 font-medium text-white hover:bg-[var(--btn-base-hover-color)] md:mx-0 md:mt-0 md:flex`}
                        >
                            Registration
                        </Link>
                    </div>
                </React.Fragment>
            )}
        </nav>
    );
}
