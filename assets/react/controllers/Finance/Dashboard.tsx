import React, { useMemo, useState } from "react";
import { Bar, Line } from "react-chartjs-2";
import "chart.js/auto";

type Metric = { label: string; amount: number | null; count: number | null; status: string; missingPaymentCount?: number; comparison?: { delta: number; percent: number | null; label: string | null } };
type DashboardData = { period: { month: string }; metrics: Record<string, Metric>; collectionTrend: Array<{ date: string; amount: number }>; flowTrend: Array<{ date: string; collections: number; expenses: number }>; warnings: Array<{ code: string; message: string }>; lists: Record<string, Array<Record<string, unknown>>> };

const formatAmount = (amount: number): string => `${new Intl.NumberFormat("fr-FR").format(amount)} FCFA`;

const Dashboard: React.FC<{ initialData: DashboardData; dataUrl: string }> = ({ initialData, dataUrl }) => {
    const [data, setData] = useState(initialData);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const labels = useMemo(() => data.collectionTrend.map((item) => item.date.slice(8)), [data.collectionTrend]);

    const changeMonth = async (offset: number): Promise<void> => {
        const current = new Date(`${data.period.month}-01T00:00:00Z`);
        current.setUTCMonth(current.getUTCMonth() + offset);
        const month = `${current.getUTCFullYear()}-${String(current.getUTCMonth() + 1).padStart(2, "0")}`;
        setLoading(true); setError(null);
        try { const response = await fetch(`${dataUrl}?month=${month}`, { headers: { Accept: "application/json" } }); if (!response.ok) throw new Error("Impossible de charger cette période."); setData(await response.json() as DashboardData); } catch (exception) { setError(exception instanceof Error ? exception.message : "Erreur de chargement."); } finally { setLoading(false); }
    };

    const collectionChart = { labels, datasets: [{ label: "Loyers encaissés", data: data.collectionTrend.map((item) => item.amount), borderColor: "rgb(59, 130, 246)", backgroundColor: "rgba(59, 130, 246, .18)", tension: .3, fill: true }] };
    const flowChart = { labels, datasets: [{ label: "Loyers encaissés", data: data.flowTrend.map((item) => item.collections), backgroundColor: "rgb(59, 130, 246)" }, { label: "Dépenses agence", data: data.flowTrend.map((item) => item.expenses), backgroundColor: "rgb(245, 158, 11)" }] };
    const options = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "bottom" as const } }, scales: { y: { beginAtZero: true, ticks: { callback: (value: string | number) => `${new Intl.NumberFormat("fr-FR").format(Number(value))}` } } } };

    return <section className="space-y-6" aria-live="polite">
        <div className="flex flex-wrap items-center justify-between gap-3"><div><h2 className="text-lg font-semibold">Évolution de la période</h2><p className="text-sm text-muted-foreground">Données opérationnelles — {data.period.month}.</p></div><div className="flex items-center gap-2"><button type="button" onClick={() => void changeMonth(-1)} disabled={loading} className="rounded-lg border border-border px-3 py-2 text-sm hover:bg-muted" aria-label="Mois précédent">←</button><span className="min-w-20 text-center text-sm font-medium">{data.period.month}</span><button type="button" onClick={() => void changeMonth(1)} disabled={loading} className="rounded-lg border border-border px-3 py-2 text-sm hover:bg-muted" aria-label="Mois suivant">→</button></div></div>
        {error && <p className="rounded-lg border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive" role="alert">{error}</p>}
        <div className="grid gap-6 lg:grid-cols-2"><article className="rounded-2xl border border-border bg-card p-5 shadow-sm"><h3 className="font-semibold">Tendance des loyers encaissés</h3><div className="mt-4 h-72">{data.collectionTrend.some((item) => item.amount > 0) ? <Line data={collectionChart} options={options} /> : <p className="flex h-full items-center justify-center text-sm text-muted-foreground">Aucune activité sur cette période.</p>}</div></article><article className="rounded-2xl border border-border bg-card p-5 shadow-sm"><h3 className="font-semibold">Flux opérationnels</h3><div className="mt-4 h-72">{data.flowTrend.some((item) => item.collections > 0 || item.expenses > 0) ? <Bar data={flowChart} options={options} /> : <p className="flex h-full items-center justify-center text-sm text-muted-foreground">Aucune activité sur cette période.</p>}</div></article></div>
        <p className="sr-only">Les graphiques sont accompagnés des valeurs textuelles présentées dans les cartes KPI.</p>
    </section>;
};

export default Dashboard;

export { formatAmount };
