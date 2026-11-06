import { usePage } from "@inertiajs/react";
import { type ClassValue, clsx } from "clsx";
import { useEffect } from "react";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export default function useFormatCurrency() {
    const { appSettings } = usePage().props;

    return (amount, currency = "BDT", locale = "en-US") => {
        currency = appSettings.currency ? appSettings.currency : currency;
        const formattedAmount = new Intl.NumberFormat(locale, {
            style: "currency",
            currency,
            currencyDisplay: "symbol",
        }).format(amount);

        const currencySymbol =
            formattedAmount.match(/[\p{Sc}]/u)?.[0] ||
            appSettings.currency_symbol;
        const numberOnly = amount.toLocaleString(locale);

        switch (appSettings.currency_format) {
            case "symbol_only":
                return `${currencySymbol} ${numberOnly}`;
            case "text_only":
                return `${currency} ${numberOnly}`;
            case "both":
            default:
                return `${currency} ${numberOnly} ${currencySymbol}`;
        }
    };
}

export function useAutoFocusError(errors, refs) {
    useEffect(() => {
        for (const key of Object.keys(errors)) {
            const ref = refs[key];

            if (ref?.current) {
                // AntD Select supports focus()
                if (typeof ref.current.focus === "function") {
                    ref.current.focus();
                    break;
                }

                // Some AntD versions expose focus on inputRef
                if (ref.current.inputRef?.current) {
                    ref.current.inputRef.current.focus();
                    break;
                }
            }
        }
    }, [errors]);
}
