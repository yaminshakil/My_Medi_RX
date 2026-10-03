import { router, usePage } from '@inertiajs/react';

interface Language {
    code: string;
    name: string;
}

interface SharedProps {
    locale: string;
    languages: Language[];
}

export default function LanguageSwitcher() {
    const { locale, languages } = usePage<SharedProps>().props;
    console.log(locale, languages);
    const handleLanguageChange = (language: string) => {
        router.get(
            `/language/${language}`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    return (
        <select
            value={locale}
            onChange={(e) => handleLanguageChange(e.target.value)}
            className="border rounded-md px-3 py-2"
        >
            {languages.map((language) => (
                <option key={language.code} value={language.code}>
                    {language.name}
                </option>
            ))}
        </select>
    );
}
