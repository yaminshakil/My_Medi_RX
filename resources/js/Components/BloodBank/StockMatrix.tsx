import { cx } from './ui';

// Each cell fills from the bottom like a bag: the taller the fill, the healthier the stock.
const levelStyle = {
    ok: { fill: 'bg-teal-500', label: 'Stock is healthy' },
    low: { fill: 'bg-amber-400', label: 'Stock is low' },
    critical: { fill: 'bg-rose-500', label: 'Stock is critical' },
};

export default function StockMatrix({ stock, onRequest }) {
    const { groups, components, matrix, totals } = stock;

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full border-separate border-spacing-1.5">
                <thead>
                    <tr>
                        <th className="w-14" />
                        {components.map((c) => (
                            <th key={c.value} scope="col" className="px-1 pb-1 text-left text-sm font-medium text-slate-600">
                                {c.label}
                                <span className="ml-2 font-normal text-slate-400">{totals.by_component[c.value]}</span>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {groups.map((group) => (
                        <tr key={group}>
                            <th scope="row" className="pr-2 text-left align-middle">
                                <span className="text-lg font-semibold text-slate-900">{group}</span>
                            </th>
                            {components.map((c) => {
                                const cell = matrix[group][c.value];
                                const style = levelStyle[cell.level];
                                // Always show a sliver when there is any stock, so 1 unit never looks like 0.
                                const height = cell.units > 0 ? Math.max(cell.fill * 100, 10) : 0;

                                return (
                                    <td key={c.value} className="p-0">
                                        <div
                                            className="relative h-16 min-w-[8rem] overflow-hidden rounded-lg bg-slate-100"
                                            title={`${group} ${c.label}: ${cell.units} unit(s). ${style.label}.`}
                                        >
                                            <div
                                                className={cx('absolute inset-x-0 bottom-0 opacity-40', style.fill)}
                                                style={{ height: `${height}%` }}
                                                aria-hidden="true"
                                            />
                                            <div className="relative flex h-full items-center justify-between gap-2 px-3">
                                                <span className="text-2xl font-semibold tabular-nums text-slate-900">
                                                    {cell.units}
                                                </span>
                                                {cell.level === 'critical' && onRequest && (
                                                    <button
                                                        type="button"
                                                        onClick={() => onRequest(group, c)}
                                                        className="rounded bg-white/90 px-2 py-1 text-xs font-semibold text-rose-800 ring-1 ring-rose-300 hover:bg-white"
                                                        aria-label={`Request ${group} ${c.label} from nearby donors`}
                                                    >
                                                        Request donors
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                );
                            })}
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
                {Object.entries(levelStyle).map(([key, s]) => (
                    <span key={key} className="inline-flex items-center gap-1.5">
                        <span className={cx('h-2.5 w-2.5 rounded-sm opacity-60', s.fill)} />
                        {s.label}
                    </span>
                ))}
            </div>
        </div>
    );
}
