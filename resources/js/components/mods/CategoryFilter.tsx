import React from 'react';
import { Card } from 'primereact/card';
import { Button } from 'primereact/button';
import { CategoryFilterState } from '@/types/mod';

interface CategoryFilterProps {
    categories: string[];
    categoryFilter: CategoryFilterState;
    onToggleCategory: (category: string) => void;
    onReset: () => void;
}

export const CategoryFilter: React.FC<CategoryFilterProps> = ({
    categories,
    categoryFilter,
    onToggleCategory,
    onReset,
}) => {
    return (
        <Card
            style={{
                border: '1px solid #374151',
                background: 'rgba(31,41,55,0.6)',
                backdropFilter: 'blur(8px)',
                borderRadius: '16px',
                padding: '1rem',
            }}
        >
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: '1rem',
                }}
            >
                <h3 style={{ margin: 0, color: '#e5e7eb', fontSize: '1.1rem' }}>
                    <i
                        className="pi pi-folder"
                        style={{ marginRight: '0.5rem' }}
                    />
                    Categories
                </h3>
                <Button
                    icon="pi pi-refresh"
                    label="Reset"
                    size="small"
                    severity="secondary"
                    text
                    onClick={onReset}
                    style={{ padding: '0.25rem 0.5rem' }}
                />
            </div>
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '0.5rem',
                }}
            >
                {categories.map((cat) => {
                    const displayName = cat === 'null' ? 'Uncategorized' : cat;
                    const state = categoryFilter[cat] || null;
                    let bgColor = 'transparent';
                    let borderColor = '#374151';
                    let icon = 'pi-circle';
                    let iconColor = '#6b7280';
                    if (state === 'include') {
                        bgColor = 'rgba(34,197,94,0.15)';
                        borderColor = '#22c55e';
                        icon = 'pi-check-circle';
                        iconColor = '#22c55e';
                    } else if (state === 'exclude') {
                        bgColor = 'rgba(239,68,68,0.15)';
                        borderColor = '#ef4444';
                        icon = 'pi-times-circle';
                        iconColor = '#ef4444';
                    }
                    return (
                        <div
                            key={cat}
                            onClick={() => onToggleCategory(cat)}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '0.5rem',
                                padding: '0.5rem 0.75rem',
                                borderRadius: '8px',
                                cursor: 'pointer',
                                backgroundColor: bgColor,
                                border: `1px solid ${borderColor}`,
                                transition: 'all 0.2s',
                            }}
                            className="hover:bg-gray-700/30"
                        >
                            <i
                                className={`pi ${icon}`}
                                style={{ color: iconColor, fontSize: '0.9rem' }}
                            />
                            <span
                                style={{ color: '#e5e7eb', fontSize: '0.9rem' }}
                            >
                                {displayName}
                            </span>
                            <span
                                style={{
                                    marginLeft: 'auto',
                                    fontSize: '0.7rem',
                                    color: '#9ca3af',
                                }}
                            >
                                {state === 'include'
                                    ? 'ON'
                                    : state === 'exclude'
                                      ? 'OFF'
                                      : ''}
                            </span>
                        </div>
                    );
                })}
            </div>
        </Card>
    );
};
