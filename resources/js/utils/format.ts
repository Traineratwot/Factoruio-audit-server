export const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

export const getStars = (popularity: number | null): number => {
    if (popularity === null || popularity === undefined) return 0;
    return Math.round(popularity / 20);
};
