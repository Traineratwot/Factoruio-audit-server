import React from 'react';
import { DataTable, DataTableSortEvent } from 'primereact/datatable';
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
    onSortChange: (field: string, direction: string) => void;
    onAuditClick: () => void;
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
    onAuditClick,
}) => {
    const totalRecords = mods.meta?.total ?? 0;
    const currentPage = mods.meta?.current_page ?? 1;
    const perPage = mods.meta?.per_page ?? 10;
    const first = (currentPage - 1) * perPage;

    const handleSort = (event: DataTableSortEvent) => {
        const newSortField = event.sortField;
        // PrimeReact: sortOrder 1 = asc, -1 = desc
        // Наш формат: 'asc' или 'desc'
        // sortOrder может быть undefined при первом клике, используем 'desc' по умолчанию
        const newSortDirection = event.sortOrder === 1
            ? 'asc'
            : event.sortOrder === -1
                ? 'desc'
                : 'desc';

        onSortChange(newSortField, newSortDirection);
        // Сбрасываем страницу при сортировке, если не на первой
        if (currentPage !== 1) {
            onPageChange(0);
        }
    };

    // Convert sortField/sortDirection to PrimeReact format
    const sortOrder = sortDirection === 'asc' ? 1 : -1;

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
                onAuditClick={onAuditClick}
            />

            <DataTable
                value={mods.data}
                loading={loading}
                tableStyle={{ minWidth: '50rem' }}
                lazy={true} // <-- отключаем клиентскую сортировку
                totalRecords={totalRecords} // <-- для корректного отображения
                stripedRows
                showGridlines={false}
                emptyMessage="No mods found"
                rowClassName={() => 'custom-row'}
                style={{ borderRadius: '12px', overflow: 'hidden' }}
                sortField={sortField}
                sortOrder={sortOrder}
                onSort={handleSort}
                resizableColumns
            >
                <Column
                    field="name"
                    header="Name"
                    body={(rowData: Mod) => <NameColumn rowData={rowData} />}
                    sortable
                    style={{ width: '35%' }}
                />
                <Column
                    field="category"
                    header="Category"
                    body={(rowData: Mod) => (
                        <CategoryColumn rowData={rowData} />
                    )}
                    sortable
                    style={{ width: '12%' }}
                />
                <Column
                    field="score"
                    header="Score"
                    body={(rowData: Mod) => <ScoreColumn rowData={rowData} />}
                    sortable
                    style={{ width: '12%' }}
                />
                <Column
                    field="downloads_count"
                    header="Downloads"
                    body={(rowData: Mod) => (
                        <DownloadsColumn rowData={rowData} />
                    )}
                    sortable
                    style={{ width: '12%' }}
                />
                <Column
                    field="popularity"
                    header="Popularity"
                    body={(rowData: Mod) => (
                        <PopularityColumn rowData={rowData} />
                    )}
                    sortable
                    style={{ width: '15%' }}
                />
                <Column
                    field="created_at"
                    header="Added"
                    body={(rowData: Mod) => <DateColumn rowData={rowData} />}
                    sortable
                    style={{ width: '12%' }}
                />
                <Column
                    header="Actions"
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
                                return `Page ${currentPage} from ${mods.meta.last_page}`;
                            },
                        }}
                    />
                </div>
            )}
        </Card>
    );
};
