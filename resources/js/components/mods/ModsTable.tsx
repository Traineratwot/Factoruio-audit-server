import React from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Card } from 'primereact/card';
import { Paginator } from 'primereact/paginator';
import { Mod, PaginatedMods } from '@/types/mod';
import { NameColumn } from './columns/NameColumn';
import { CategoryColumn } from './columns/CategoryColumn';
import { ScoreColumn } from './columns/ScoreColumn';
import { DownloadsColumn } from './columns/DownloadsColumn';
import { PopularityColumn } from './columns/PopularityColumn';
import { DateColumn } from './columns/DateColumn';
import { ActionColumn } from './columns/ActionColumn';
import { TableHeader } from './TableHeader';

interface ModsTableProps {
    mods: PaginatedMods;
    loading: boolean;
    searchQuery: string;
    onSearchChange: (value: string) => void;
    onClearSearch: () => void;
    onPageChange: (page: number) => void;
    sortField: string;
    sortDirection: string;
    onSortChange: (field: string) => void;
}

export const ModsTable: React.FC<ModsTableProps> = ({
    mods,
    loading,
    searchQuery,
    onSearchChange,
    onClearSearch,
    onPageChange,
    sortField,
    sortDirection,
    onSortChange,
}) => {
    const totalRecords = mods.meta?.total ?? 0;
    const currentPage = mods.meta?.current_page ?? 1;
    const perPage = mods.meta?.per_page ?? 10;
    const first = (currentPage - 1) * perPage;

    return (
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
            <TableHeader
                searchQuery={searchQuery}
                onSearchChange={onSearchChange}
                onClearSearch={onClearSearch}
                totalRecords={totalRecords}
            />

            <DataTable
                value={mods.data}
                loading={loading}
                tableStyle={{ minWidth: '50rem' }}
                stripedRows
                showGridlines={false}
                emptyMessage="No mods found"
                rowClassName={() => 'custom-row'}
                style={{ borderRadius: '12px', overflow: 'hidden' }}
            >
                <Column
                    field="name"
                    header={
                        <span
                            style={{
                                fontWeight: '600',
                                color: '#9ca3af',
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('name')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'name'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'name' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Name
                        </span>
                    }
                    body={(rowData: Mod) => <NameColumn rowData={rowData} />}
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
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('category')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'category'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'category' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Category
                        </span>
                    }
                    body={(rowData: Mod) => (
                        <CategoryColumn rowData={rowData} />
                    )}
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
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('score')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'score'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'score' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Score
                        </span>
                    }
                    body={(rowData: Mod) => <ScoreColumn rowData={rowData} />}
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
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('downloads_count')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'downloads_count'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'downloads_count' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Downloads
                        </span>
                    }
                    body={(rowData: Mod) => (
                        <DownloadsColumn rowData={rowData} />
                    )}
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
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('popularity')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'popularity'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'popularity' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Popularity
                        </span>
                    }
                    body={(rowData: Mod) => (
                        <PopularityColumn rowData={rowData} />
                    )}
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
                                cursor: 'pointer',
                            }}
                            onClick={() => onSortChange('created_at')}
                        >
                            <i
                                className={`pi ${
                                    sortField === 'created_at'
                                        ? sortDirection === 'asc'
                                            ? 'pi-sort-up-alt'
                                            : 'pi-sort-down-alt'
                                        : 'pi-sort'
                                }`}
                                style={{
                                    marginRight: '0.5rem',
                                    color: sortField === 'created_at' ? '#06b6d4' : '#6b7280',
                                }}
                            />
                            Added
                        </span>
                    }
                    body={(rowData: Mod) => <DateColumn rowData={rowData} />}
                    sortable
                    style={{ width: '12%' }}
                />
                <Column
                    header={
                        <span style={{ fontWeight: '600', color: '#9ca3af' }}>
                            <i
                                className="pi pi-cog"
                                style={{ marginRight: '0.5rem' }}
                            />
                            Actions
                        </span>
                    }
                    body={(rowData: Mod) => <ActionColumn rowData={rowData} />}
                    style={{ width: '10%', textAlign: 'center' }}
                />
            </DataTable>

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
                        onPageChange={(e) => onPageChange(e.page)}
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
    );
};
