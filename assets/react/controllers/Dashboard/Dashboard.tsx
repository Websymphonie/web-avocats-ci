import React from "react";
const Dashboard: React.FC = () => {
    return (
        <div className="space-y-5">
            <section className="overflow-hidden rounded-2xl border border-border bg-card shadow-sm" aria-labelledby="dashboard-statistics-title">
                <div className="flex flex-col gap-4 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <div className="flex items-center gap-2">
                            <h2 id="dashboard-statistics-title" className="text-lg font-semibold tracking-[-0.02em]">Vue d’ensemble</h2>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">Les repères essentiels pour piloter l’activité.</p>
                    </div>
                </div>
            </section>
        </div>
    );
};

export default Dashboard;
