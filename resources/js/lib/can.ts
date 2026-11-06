import { usePage } from "@inertiajs/react";

export function useCan(permission: string): boolean {
    const page = usePage();
    //const auth = page.props.auth;
    const { auth } = page.props as {
        auth: {
            permissions: string[];
        };
    };

    return auth.permissions.includes(permission);
}
