import { Head, router } from '@inertiajs/react';
import { PrimeReactProvider } from 'primereact/api';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { Button } from 'primereact/button';
import { Card } from 'primereact/card';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { useEffect, useState } from 'react';
import { Paginator } from 'primereact/paginator';
import { ProgressBar } from 'primereact/progressbar';
import { Tag } from 'primereact/tag';
import { Tooltip } from 'primereact/tooltip';

interface Mod {
    id: number;
    name: string;
    owner: string;
    latest_version: string | null;
    category: string | null;
    title: string | null;
    summary: string | null;
    downloads_count: number | null;
    popularity: number | null;
    created_at: string;
    updated_at: string;
    report_url: string;
    score?: number | null; // допускаем, что может быть
}

interface PaginatedMods {
    data: Mod[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Welcome({
    mods,
    search,
}: {
    mods: PaginatedMods;
    search: string;
}) {
    const [searchQuery, setSearchQuery] = useState(search || '');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const timeout = setTimeout(() => {
            if (searchQuery !== search) {
                setLoading(true);
                router.get(
                    window.location.pathname,
                    { search: searchQuery },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        onFinish: () => setLoading(false),
                    },
                );
            }
        }, 500);
        return () => clearTimeout(timeout);
    }, [searchQuery]);

    const handlePageChange = (event: { page: number }) => {
        if (!mods.meta.total) return;
        setLoading(true);
        router.get(
            window.location.pathname,
            { search: searchQuery, page: event.page + 1 },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
            },
        );
    };

    const clearSearch = () => {
        setSearchQuery('');
    };

    const formatDate = (date: string) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    // ---------- Шаблоны столбцов с улучшенным дизайном ----------
    const nameTemplate = (rowData: Mod) => {
        return (
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem',
                }}
            >
                <div
                    style={{
                        width: '2.5rem',
                        height: '2.5rem',
                        borderRadius: '50%',
                        background: 'linear-gradient(135deg, #06b6d4, #3b82f6)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: '#fff',
                        fontWeight: 'bold',
                        fontSize: '1rem',
                        flexShrink: 0,
                    }}
                >
                    {(rowData.title || rowData.name).charAt(0).toUpperCase()}
                </div>
                <div>
                    <div style={{ fontWeight: '600', color: '#e5e7eb' }}>
                        {rowData.title || rowData.name}
                    </div>
                    <div style={{ fontSize: '0.75rem', color: '#9ca3af' }}>
                        {rowData.owner && `by ${rowData.owner}`}
                        {rowData.latest_version &&
                            ` · v${rowData.latest_version}`}
                    </div>
                </div>
            </div>
        );
    };

    const categoryTemplate = (rowData: Mod) => {
        const category = rowData.category || 'Uncategorized';
        const colors: Record<string, string> = {
            gameplay: '#8b5cf6',
            utility: '#3b82f6',
            graphics: '#ec4899',
            audio: '#f59e0b',
            map: '#10b981',
            scenario: '#ef4444',
        };
        const color = colors[category.toLowerCase()] || '#6b7280';
        return (
            <Tag
                value={category}
                style={{
                    backgroundColor: color,
                    color: '#fff',
                    fontWeight: '500',
                    borderRadius: '20px',
                    padding: '0.25rem 0.75rem',
                    fontSize: '0.75rem',
                }}
            />
        );
    };

    const scoreTemplate = (rowData: Mod) => {
        const score = rowData.score;
        if (score === null || score === undefined)
            return <span style={{ color: '#6b7280' }}>—</span>;
        const color =
            score >= 70 ? '#22c55e' : score >= 40 ? '#f16338' : '#ef4444';
        return (
            <div
                style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}
            >
                <span style={{ fontWeight: 'bold', color, minWidth: '2.5rem' }}>
                    {score.toFixed(1)}
                </span>
                <ProgressBar
                    value={score}
                    showValue={false}
                    style={{ height: '6px', width: '4rem' }}
                    color={color}
                />
            </div>
        );
    };

    const downloadsTemplate = (rowData: Mod) => {
        const count = rowData.downloads_count || 0;
        return (
            <div
                style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}
            >
                <i className="pi pi-download" style={{ color: '#60a5fa' }} />
                <span style={{ fontWeight: '500' }}>
                    {count.toLocaleString()}
                </span>
            </div>
        );
    };

    const popularityTemplate = (rowData: Mod) => {
        const pop = rowData.popularity;
        if (pop === null || pop === undefined)
            return <span style={{ color: '#6b7280' }}>N/A</span>;
        const stars = Math.round(pop / 20);
        return (
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.25rem',
                }}
            >
                {[...Array(5)].map((_, i) => (
                    <i
                        key={i}
                        className="pi pi-star-fill"
                        style={{
                            color: i < stars ? '#fbbf24' : '#4b5563',
                            fontSize: '0.9rem',
                        }}
                    />
                ))}
                <span
                    style={{
                        marginLeft: '0.25rem',
                        fontSize: '0.8rem',
                        color: '#9ca3af',
                    }}
                >
                    {pop.toFixed(1)}
                </span>
            </div>
        );
    };

    const dateTemplate = (rowData: Mod) => {
        return (
            <div style={{ fontSize: '0.85rem', color: '#d1d5db' }}>
                {formatDate(rowData.created_at)}
            </div>
        );
    };

    const actionTemplate = (rowData: Mod) => {
        const handleClick = () => {
            router.visit(rowData.report_url);
        };
        return (
            <Button
                label="Report"
                icon="pi pi-chart-line"
                size="small"
                onClick={handleClick}
                severity="info"
                text
                raised
                style={{
                    borderRadius: '20px',
                    padding: '0.25rem 1rem',
                    transition: 'all 0.2s',
                }}
                className="p-button-outlined"
            />
        );
    };

    // Пагинация
    const currentPage = mods.meta?.current_page ?? 1;
    const perPage = mods.meta?.per_page ?? 10;
    const first = (currentPage - 1) * perPage;
    const totalRecords = mods.meta?.total ?? 0;

    return (
        <>
            <Head title="Mods | Overview" />
            <PrimeReactProvider>
                <Tooltip target=".custom-tooltip" />
                <div
                    style={{
                        minHeight: '100vh',
                        padding: '1.5rem',
                    }}
                >
                    <div style={{ maxWidth: '80rem', margin: '0 auto' }}>
                        {/* Header */}
                        <div
                            style={{
                                textAlign: 'center',
                                marginBottom: '2rem',
                            }}
                        >
                            <h1
                                style={{
                                    fontSize: '2.5rem',
                                    fontWeight: 'bold',
                                    marginBottom: '0.5rem',
                                    background:
                                        'linear-gradient(135deg, #06b6d4, #3b82f6)',
                                    WebkitBackgroundClip: 'text',
                                    backgroundClip: 'text',
                                    color: 'transparent',
                                    textShadow: '0 0 20px rgba(6,182,212,0.3)',
                                }}
                            >
                                Mods Catalog
                            </h1>
                            <p style={{ color: '#9ca3af', fontSize: '1.1rem' }}>
                                Explore popular mods and their reports
                            </p>
                        </div>

                        {/* Карточка с таблицей */}
                        <Card
                            style={{
                                border: '1px solid #374151',
                                background: 'rgba(31,41,55,0.6)',
                                backdropFilter: 'blur(8px)',
                                boxShadow: '0 8px 32px rgba(0,0,0,0.4)',
                                borderRadius: '16px',
                                overflow: 'hidden',
                            }}
                        >
                            {/* Поиск и статистика */}
                            <div
                                style={{
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    gap: '1rem',
                                    padding: '0.5rem 0 1.5rem 0',
                                }}
                            >
                                <div
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '1rem',
                                    }}
                                >
                                    <div style={{ width: '20rem' }}>
                                        <IconField iconPosition="left">
                                            <InputIcon className="pi pi-search" />
                                            <InputText
                                                value={searchQuery}
                                                onChange={(e) =>
                                                    setSearchQuery(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Search by name, author, description..."
                                                style={{
                                                    width: '100%',
                                                    borderRadius: '30px',
                                                    paddingLeft: '2.5rem',
                                                    background: '#1f2937',
                                                    borderColor: '#374151',
                                                    color: '#e5e7eb',
                                                }}
                                            />
                                        </IconField>
                                    </div>
                                    {searchQuery && (
                                        <Button
                                            icon="pi pi-times"
                                            label="Clear"
                                            severity="secondary"
                                            outlined
                                            onClick={clearSearch}
                                            size="small"
                                            style={{ borderRadius: '30px' }}
                                        />
                                    )}
                                </div>
                                <div
                                    style={{
                                        color: '#9ca3af',
                                        fontSize: '0.9rem',
                                        background: '#1f2937',
                                        padding: '0.3rem 1rem',
                                        borderRadius: '30px',
                                        border: '1px solid #374151',
                                    }}
                                >
                                    <i
                                        className="pi pi-database"
                                        style={{ marginRight: '0.5rem' }}
                                    />
                                    {totalRecords} mods
                                </div>
                            </div>

                            {/* Таблица */}
                            <DataTable
                                value={mods.data}
                                loading={loading}
                                tableStyle={{ minWidth: '50rem' }}
                                stripedRows
                                showGridlines={false}
                                emptyMessage="No mods found"
                                rowClassName={() => 'custom-row'}
                                style={{
                                    borderRadius: '12px',
                                    overflow: 'hidden',
                                }}
                            >
                                <Column
                                    field="name"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-tag"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Name
                                        </span>
                                    }
                                    body={nameTemplate}
                                    sortable
                                    style={{ width: '35%' }}
                                />
                                <Column
                                    field="category"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-folder"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Category
                                        </span>
                                    }
                                    body={categoryTemplate}
                                    sortable
                                    style={{ width: '12%' }}
                                />
                                <Column
                                    field="score"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-star"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Score
                                        </span>
                                    }
                                    body={scoreTemplate}
                                    sortable
                                    style={{ width: '12%' }}
                                />
                                <Column
                                    field="downloads_count"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-download"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Downloads
                                        </span>
                                    }
                                    body={downloadsTemplate}
                                    sortable
                                    style={{ width: '12%' }}
                                />
                                <Column
                                    field="popularity"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-heart"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Popularity
                                        </span>
                                    }
                                    body={popularityTemplate}
                                    sortable
                                    style={{ width: '15%' }}
                                />
                                <Column
                                    field="created_at"
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-calendar"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Added
                                        </span>
                                    }
                                    body={dateTemplate}
                                    sortable
                                    style={{ width: '12%' }}
                                />
                                <Column
                                    header={
                                        <span
                                            style={{
                                                fontWeight: '600',
                                                color: '#9ca3af',
                                            }}
                                        >
                                            <i
                                                className="pi pi-cog"
                                                style={{
                                                    marginRight: '0.5rem',
                                                }}
                                            />
                                            Actions
                                        </span>
                                    }
                                    body={actionTemplate}
                                    style={{
                                        width: '10%',
                                        textAlign: 'center',
                                    }}
                                />
                            </DataTable>

                            {/* Пагинатор */}
                            {totalRecords > perPage && (
                                <div
                                    style={{
                                        marginTop: '1.5rem',
                                        display: 'flex',
                                        justifyContent: 'center',
                                    }}
                                >
                                    <Paginator
                                        first={first}
                                        rows={perPage}
                                        totalRecords={totalRecords}
                                        onPageChange={handlePageChange}
                                        template={{
                                            layout: 'FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport',
                                            RowsPerPageDropdown: false,
                                            CurrentPageReport: (options) => {
                                                return `Страница ${currentPage} из ${mods.meta.last_page}`;
                                            },
                                        }}
                                    />
                                </div>
                            )}
                        </Card>

                        {/* Футер */}
                        <div
                            style={{
                                marginTop: '2rem',
                                textAlign: 'center',
                                fontSize: '0.9rem',
                                color: '#6b7280',
                                borderTop: '1px solid #374151',
                                paddingTop: '1.5rem',
                            }}
                        >
                            <i
                                className="pi pi-sync"
                                style={{ marginRight: '0.5rem' }}
                            />
                            Data updates automatically
                        </div>
                    </div>
                </div>
            </PrimeReactProvider>
        </>
    );
}
