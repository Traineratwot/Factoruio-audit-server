import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import { CategoryFilterState, ReportFilterValue } from '@/types/mod';

export const useModsFilter = (
    initialSearch: string,
    initialCategoryInclude: string[],
    initialCategoryExclude: string[],
    initialSortField: string = 'created_at',
    initialSortDirection: string = 'desc',
    initialReportFilter: ReportFilterValue = 'all',
) => {
    const [searchQuery, setSearchQuery] = useState(initialSearch || '');
    const [categoryFilter, setCategoryFilter] = useState<CategoryFilterState>(
        () => {
            const filter: CategoryFilterState = {};
            initialCategoryInclude.forEach((cat) => {
                filter[cat] = 'include';
            });
            initialCategoryExclude.forEach((cat) => {
                filter[cat] = 'exclude';
            });
            return filter;
        },
    );
    const [sortField, setSortField] = useState(initialSortField);
    const [sortDirection, setSortDirection] = useState(initialSortDirection);
    const [reportFilter, setReportFilter] =
        useState<ReportFilterValue>(initialReportFilter);
    const [loading, setLoading] = useState(false);

    const getCurrentParams = () => {
        const params = new URLSearchParams(window.location.search);
        return {
            search: params.get('search') || '',
            include: params.getAll('category_include[]'),
            exclude: params.getAll('category_exclude[]'),
            sortField: params.get('sort_field') || 'created_at',
            sortDirection: params.get('sort_direction') || 'desc',
            reportFilter:
                (params.get('report_filter') as ReportFilterValue) || 'all',
        };
    };

    const updateUrl = (
        page?: number,
        newSortField?: string,
        newSortDirection?: string,
        newReportFilter?: ReportFilterValue,
    ) => {
        const params: Record<string, string | string[]> = {};
        if (searchQuery) params.search = searchQuery;

        const include: string[] = [];
        const exclude: string[] = [];
        Object.entries(categoryFilter).forEach(([cat, state]) => {
            if (state === 'include') include.push(cat);
            else if (state === 'exclude') exclude.push(cat);
        });
        if (include.length) params.category_include = include;
        if (exclude.length) params.category_exclude = exclude;
        if (page) params.page = String(page);

        const currentSortField = newSortField ?? sortField;
        const currentSortDirection = newSortDirection ?? sortDirection;
        if (currentSortField && currentSortField !== 'created_at')
            params.sort_field = currentSortField;
        if (currentSortDirection && currentSortDirection !== 'desc')
            params.sort_direction = currentSortDirection;

        const currentReportFilter = newReportFilter ?? reportFilter;
        if (currentReportFilter && currentReportFilter !== 'all')
            params.report_filter = currentReportFilter;

        const current = getCurrentParams();
        const hasChanged =
            searchQuery !== current.search ||
            JSON.stringify(include.sort()) !==
                JSON.stringify(current.include.sort()) ||
            JSON.stringify(exclude.sort()) !==
                JSON.stringify(current.exclude.sort()) ||
            currentSortField !== current.sortField ||
            currentSortDirection !== current.sortDirection ||
            currentReportFilter !== current.reportFilter ||
            (page &&
                page !==
                    parseInt(
                        new URLSearchParams(window.location.search).get(
                            'page',
                        ) || '1',
                    ));

        if (hasChanged) {
            setLoading(true);
            router.get(window.location.pathname, params, {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
            });
        }
    };

    useEffect(() => {
        const timeout = setTimeout(() => {
            updateUrl();
        }, 500);
        return () => clearTimeout(timeout);
    }, [searchQuery, categoryFilter, reportFilter]);

    const resetFilters = () => {
        setCategoryFilter({});
        setSortField('created_at');
        setSortDirection('desc');
        setReportFilter('all');
        updateUrl(undefined, 'created_at', 'desc', 'all');
    };

    const toggleCategory = (category: string) => {
        setCategoryFilter((prev) => {
            const current = prev[category] || null;
            let next: 'include' | 'exclude' | null = null;
            if (current === null) next = 'include';
            else if (current === 'include') next = 'exclude';
            else if (current === 'exclude') next = null;

            const newFilter = { ...prev, [category]: next };
            updateCategoryFilter(newFilter);
            return newFilter;
        });
    };

    const updateCategoryFilter = (newFilter: CategoryFilterState) => {
        const include: string[] = [];
        const exclude: string[] = [];
        Object.entries(newFilter).forEach(([cat, state]) => {
            if (state === 'include') include.push(cat);
            else if (state === 'exclude') exclude.push(cat);
        });

        const params: Record<string, string | string[]> = {};
        if (searchQuery) params.search = searchQuery;
        if (include.length) params.category_include = include;
        if (exclude.length) params.category_exclude = exclude;
        if (sortField && sortField !== 'created_at') params.sort_field = sortField;
        if (sortDirection && sortDirection !== 'desc')
            params.sort_direction = sortDirection;
        if (reportFilter && reportFilter !== 'all')
            params.report_filter = reportFilter;

        const current = getCurrentParams();
        const hasChanged =
            searchQuery !== current.search ||
            JSON.stringify(include.sort()) !==
                JSON.stringify(current.include.sort()) ||
            JSON.stringify(exclude.sort()) !==
                JSON.stringify(current.exclude.sort()) ||
            sortField !== current.sortField ||
            sortDirection !== current.sortDirection ||
            reportFilter !== current.reportFilter;

        if (hasChanged) {
            setLoading(true);
            router.get(window.location.pathname, params, {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
            });
        }
    };

    const handlePageChange = (page: number) => {
        updateUrl(page + 1);
    };

    const handleSort = (field: string, direction?: string) => {
        setSortField(field);
        setSortDirection(direction || 'desc');
        updateUrl(undefined, field, direction || 'desc');
    };

    const handleReportFilterChange = (value: ReportFilterValue) => {
        setReportFilter(value);
        updateUrl(undefined, undefined, undefined, value);
    };

    const clearSearch = () => {
        setSearchQuery('');
    };

    return {
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
        reportFilter,
        handleReportFilterChange,
    };
};
