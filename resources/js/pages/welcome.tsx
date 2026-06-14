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
}

interface PaginatedMods {
    data: Mod[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
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

    const nameTemplate = (rowData: Mod) => {
        return (
            <span style={{ fontWeight: 'bold', color: '#06b6d4' }}>
                {rowData.title || rowData.name}
            </span>
        );
    };

    const downloadsTemplate = (rowData: Mod) => {
        return rowData.downloads_count?.toLocaleString() || '0';
    };

    const popularityTemplate = (rowData: Mod) => {
        return rowData.popularity ? rowData.popularity.toFixed(1) : 'N/A';
    };

    // Actions column with Report button
    const actionTemplate = (rowData: Mod) => {
        const reportUrl = rowData.report_url;
        console.log(reportUrl);
        const handleClick = () => {
            router.visit(reportUrl);
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
            />
        );
    };

    return (
        <>
            <Head title="Mods | Overview" />
            <PrimeReactProvider>
                <div
                    style={{
                        minHeight: '100vh',
                        padding: '1.5rem',
                        background: '#111827',
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
                                    fontSize: '2.25rem',
                                    fontWeight: 'bold',
                                    marginBottom: '0.5rem',
                                    background:
                                        'linear-gradient(135deg, #06b6d4, #3b82f6)',
                                    WebkitBackgroundClip: 'text',
                                    backgroundClip: 'text',
                                    color: 'transparent',
                                }}
                            >
                                Mods Catalog
                            </h1>
                            <p style={{ color: '#9ca3af' }}>
                                Explore popular mods and their reports
                            </p>
                        </div>

                        {/* Card with table */}
                        <Card
                            style={{
                                border: '1px solid #374151',
                                background: 'rgba(31,41,55,0.5)',
                                backdropFilter: 'blur(4px)',
                            }}
                        >
                            {/* Search panel */}
                            <div
                                style={{
                                    display: 'flex',
                                    flexWrap: 'wrap',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    gap: '1rem',
                                    marginBottom: '1.5rem',
                                }}
                            >
                                <div
                                    style={{ width: '100%', maxWidth: '20rem' }}
                                >
                                    <IconField iconPosition="left">
                                        <InputIcon className="pi pi-search" />
                                        <InputText
                                            value={searchQuery}
                                            onChange={(e) =>
                                                setSearchQuery(e.target.value)
                                            }
                                            placeholder="Search by name, author or description..."
                                            style={{ width: '100%' }}
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
                                    />
                                )}
                                <div
                                    style={{
                                        color: '#9ca3af',
                                        fontSize: '0.875rem',
                                    }}
                                >
                                    Total mods: {mods.total}
                                </div>
                            </div>

                            {/* Mods table */}
                            <DataTable
                                value={mods.data}
                                loading={loading}
                                tableStyle={{ minWidth: '50rem' }}
                                stripedRows
                                showGridlines
                                emptyMessage="No mods found"
                            >
                                <Column
                                    field="name"
                                    header="Name"
                                    body={nameTemplate}
                                    sortable
                                    style={{ width: '30%' }}
                                />
                                <Column
                                    field="latest_version"
                                    header="Version"
                                    sortable
                                    style={{ width: '10%' }}
                                />
                                <Column
                                    field="category"
                                    header="Category"
                                    sortable
                                    style={{ width: '10%' }}
                                />
                                <Column
                                    field="downloads_count"
                                    header="Downloads"
                                    body={downloadsTemplate}
                                    sortable
                                    style={{ width: '12%' }}
                                />
                                <Column
                                    field="popularity"
                                    header="Popularity"
                                    body={popularityTemplate}
                                    sortable
                                    style={{ width: '10%' }}
                                />
                                <Column
                                    field="created_at"
                                    header="Added"
                                    body={(rowData: Mod) =>
                                        formatDate(rowData.created_at)
                                    }
                                    sortable
                                    style={{ width: '13%' }}
                                />
                                <Column
                                    header="Actions"
                                    body={actionTemplate}
                                    style={{
                                        width: '15%',
                                        textAlign: 'center',
                                    }}
                                />
                            </DataTable>

                            {/* PrimeReact Paginator */}
                            <div
                                style={{
                                    marginTop: '1.5rem',
                                    display: 'flex',
                                    justifyContent: 'center',
                                }}
                            >
                                <Paginator
                                    first={
                                        (mods.current_page - 1) * mods.per_page
                                    }
                                    rows={mods.per_page}
                                    totalRecords={mods.total}
                                    onPageChange={handlePageChange}
                                    template={{
                                        layout: 'PrevPageLink PageLinks NextPageLink CurrentPageReport',
                                        RowsPerPageDropdown: false,
                                    }}
                                />
                            </div>
                        </Card>

                        {/* Footer */}
                        <div
                            style={{
                                marginTop: '2rem',
                                textAlign: 'center',
                                fontSize: '0.875rem',
                                color: '#6b7280',
                            }}
                        >
                            Data updates automatically
                        </div>
                    </div>
                </div>
            </PrimeReactProvider>
        </>
    );
}
