import React from 'react';
import { Head } from '@inertiajs/react';
import { PrimeReactProvider } from 'primereact/api';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';
import { Tooltip } from 'primereact/tooltip';
import { ModsTable } from '@/components/mods/ModsTable';
import { CategoryFilter } from '@/components/mods/CategoryFilter';
import { useModsFilter } from '@/hooks/useModsFilter';
import { PaginatedMods } from '@/types/mod';
import Container from '@/components/ui/Container';

interface WelcomeProps {
    mods: PaginatedMods;
    search: string;
    categoryInclude?: string[];
    categoryExclude?: string[];
    category_all: string[];
    sort_field: string;
    sort_direction: string;
}

export default function Welcome({
    mods,
    search,
    categoryInclude = [],
    categoryExclude = [],
    category_all = [],
    sort_field = 'created_at',
    sort_direction = 'desc',
}: WelcomeProps) {
    const {
        searchQuery,
        setSearchQuery,
        categoryFilter,
        toggleCategory,
        resetFilters,
        loading,
        handlePageChange,
        clearSearch,
        sortField,
        sortDirection,
        handleSort,
    } = useModsFilter(search, categoryInclude, categoryExclude, sort_field, sort_direction);

    const allCategories = Array.from(new Set(category_all)).sort();

    return (
        <>
            <Head title="Mods | Overview" />
            <PrimeReactProvider>
                <Tooltip target=".custom-tooltip" />
                <Container
                    maxWidth="120rem"
                    padding="1.5rem"
                    className="min-h-screen"
                >
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

                    {/* Main layout */}
                    <div
                        style={{
                            display: 'flex',
                            gap: '2rem',
                            alignItems: 'flex-start',
                        }}
                    >
                        <div style={{ width: '250px', flexShrink: 0 }}>
                            <CategoryFilter
                                categories={allCategories}
                                categoryFilter={categoryFilter}
                                onToggleCategory={toggleCategory}
                                onReset={resetFilters}
                            />
                        </div>
                        <div style={{ flex: 1 }}>
                            <ModsTable
                                mods={mods}
                                loading={loading}
                                searchQuery={searchQuery}
                                onSearchChange={setSearchQuery}
                                onClearSearch={clearSearch}
                                onPageChange={handlePageChange}
                                sortField={sortField}
                                sortDirection={sortDirection}
                                onSortChange={handleSort}
                            />
                        </div>
                    </div>

                    {/* Footer */}
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
                </Container>
            </PrimeReactProvider>
        </>
    );
}
