import { Link } from '@inertiajs/react';

// Small shared UI kit for the blood bank pages. Plain Tailwind, no extra dependencies.

export const cx = (...parts) => parts.filter(Boolean).join(' ');

// Drops empty values so query strings stay short: { a: '', b: 1 } -> { b: 1 }
export const clean = (obj) =>
    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v !== null && v !== undefined));

export const inputCls =
    'block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-rose-700 focus:ring-rose-700';

export const formatDateTime = (iso) =>
    iso ? new Date(iso).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '—';

export const formatHoursLeft = (hours) => {
    if (hours < 0) return 'Expired';
    if (hours < 24) return `${hours}h left`;
    return `${Math.floor(hours / 24)}d left`;
};

const tones = {
    slate: 'bg-slate-100 text-slate-700',
    teal: 'bg-teal-100 text-teal-800',
    amber: 'bg-amber-100 text-amber-800',
    rose: 'bg-rose-100 text-rose-800',
    sky: 'bg-sky-100 text-sky-800',
};

export function Pill({ tone = 'slate', children }) {
    return (
        <span className={cx('inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium', tones[tone])}>
            {children}
        </span>
    );
}

const statusTone = { available: 'teal', quarantined: 'amber', issued: 'sky', expired: 'rose', discarded: 'slate' };

export function StatusPill({ status }) {
    return <Pill tone={statusTone[status] ?? 'slate'}>{status.charAt(0).toUpperCase() + status.slice(1)}</Pill>;
}

const expiryTone = { ok: 'slate', warning: 'amber', critical: 'rose', expired: 'rose' };

export function ExpiryPill({ state, hours }) {
    return <Pill tone={expiryTone[state] ?? 'slate'}>{formatHoursLeft(hours)}</Pill>;
}

const buttonStyles = {
    primary: 'bg-rose-800 text-white hover:bg-rose-900',
    secondary: 'bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50',
    danger: 'bg-white text-rose-700 ring-1 ring-rose-300 hover:bg-rose-50',
};

// Renders an Inertia <Link> when href is given, otherwise a <button>.
export function Btn({ variant = 'primary', href, className, type = 'button', ...props }) {
    const cls = cx(
        'inline-flex items-center justify-center rounded-md px-3.5 py-2 text-sm font-semibold disabled:opacity-50',
        buttonStyles[variant],
        className
    );
    return href ? <Link href={href} className={cls} {...props} /> : <button type={type} className={cls} {...props} />;
}

export function Card({ title, actions, children, className }) {
    return (
        <section className={cx('rounded-lg bg-white shadow-sm ring-1 ring-slate-200', className)}>
            {(title || actions) && (
                <div className="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
                    <h3 className="text-base font-semibold text-slate-900">{title}</h3>
                    {actions}
                </div>
            )}
            <div className="p-5">{children}</div>
        </section>
    );
}

export function Field({ label, error, hint, children, className }) {
    return (
        <label className={cx('block', className)}>
            <span className="mb-1 block text-sm font-medium text-slate-700">{label}</span>
            {children}
            {hint && !error && <span className="mt-1 block text-xs text-slate-500">{hint}</span>}
            {error && <span className="mt-1 block text-xs text-rose-700">{error}</span>}
        </label>
    );
}

// options: ['A+', ...] or [{ value, label }, ...]
export function Select({ options, placeholder, className, ...props }) {
    return (
        <select className={cx(inputCls, className)} {...props}>
            {placeholder !== undefined && <option value="">{placeholder}</option>}
            {options.map((o) => {
                const opt = typeof o === 'object' ? o : { value: o, label: o };
                return (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                );
            })}
        </select>
    );
}

export function Pagination({ links }) {
    if (!links || links.length <= 3) return null;
    return (
        <nav className="flex flex-wrap gap-1" aria-label="Pagination">
            {links.map((l, i) => {
                const label = l.label.replace('&laquo;', '«').replace('&raquo;', '»');
                const cls = cx(
                    'rounded-md px-3 py-1.5 text-sm',
                    l.active ? 'bg-rose-800 font-semibold text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50',
                    !l.url && 'pointer-events-none opacity-40'
                );
                return l.url ? (
                    <Link key={i} href={l.url} className={cls} preserveScroll>
                        {label}
                    </Link>
                ) : (
                    <span key={i} className={cls}>
                        {label}
                    </span>
                );
            })}
        </nav>
    );
}

export function EmptyState({ children }) {
    return <p className="py-10 text-center text-sm text-slate-500">{children}</p>;
}

export const th = 'px-3 py-2 text-left text-xs font-semibold text-slate-500';
export const td = 'px-3 py-3 text-sm text-slate-700';
