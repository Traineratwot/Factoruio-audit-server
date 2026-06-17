import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import { CategoryFilterState } from '@/types/mod';

export const useModsFilter = (
    initialSearch: string,
    initialCategoryInclude: string[],
    initialCategoryExclude: string[],
    initialSortField: string = 'created_at',
    initialSortDirection: string = 'desc',
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
    const [loading, setLoading] = useState(false);

    // Функция для получения текущих параметров из URL
    const getCurrentParams = () => {
        const params = new URLSearchParams(window.location.search);
        return {
            search: params.get('search') || '',
            include: params.getAll('category_include[]'),
            exclude: params.getAll('category_exclude[]'),
        };
    };

    // Обновление URL и выполнение запроса
    const updateUrl = (page?: number) => {
        const params: any = {};
        if (searchQuery) params.search = searchQuery;

        const include: string[] = [];
        const exclude: string[] = [];
        Object.entries(categoryFilter).forEach(([cat, state]) => {
            if (state === 'include') include.push(cat);
            else if (state === 'exclude') exclude.push(cat);
        });
        if (include.length) params.category_include = include;
        if (exclude.length) params.category_exclude = exclude;
        if (page) params.page = page;
        if (sortField && sortField !== 'created_at') params.sort_field = sortField;
        if (sortDirection && sortDirection !== 'desc') params.sort_direction = sortDirection;

        const current = getCurrentParams();
        const hasChanged =
            searchQuery !== current.search ||
            JSON.stringify(include.sort()) !==
                JSON.stringify(current.include.sort()) ||
            JSON.stringify(exclude.sort()) !==
                JSON.stringify(current.exclude.sort()) ||
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

    // Дебаунс для поиска и фильтров
    useEffect(() => {
        const timeout = setTimeout(() => {
            updateUrl();
        }, 500);
        return () => clearTimeout(timeout);
    }, [searchQuery, categoryFilter]);

    // Сброс фильтров
    const resetFilters = () => {
        setCategoryFilter({});
        setSortField('created_at');
        setSortDirection('desc');
    };

    // Переключение состояния категории
    const toggleCategory = (category: string) => {
        setCategoryFilter((prev) => {
            const current = prev[category] || null;
            let next: 'include' | 'exclude' | null = null;
            if (current === null) next = 'include';
            else if (current === 'include') next = 'exclude';
            else if (current === 'exclude') next = null;
            return { ...prev, [category]: next };
        });
    };

    // Смена страницы
    const handlePageChange = (page: number) => {
        updateUrl(page + 1);
    };

    // Смена сортировки
    const handleSort = (field: string) => {
        if (sortField === field) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortField(field);
            setSortDirection('desc');
        }
    };

    // Очистка поиска
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
    };
};
