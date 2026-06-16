import React from 'react';
import { Mod } from '@/types/mod';
import { getStars } from '@/utils/format';

export const PopularityColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
    const pop = rowData.popularity;
    if (pop === null || pop === undefined)
        return <span style={{ color: '#6b7280' }}>N/A</span>;
    const stars = getStars(pop);
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
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
