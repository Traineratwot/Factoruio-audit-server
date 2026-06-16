import React from 'react';
import { Mod } from '@/types/mod';

export const NameColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
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
                    {rowData.latest_version && ` · v${rowData.latest_version}`}
                </div>
            </div>
        </div>
    );
};
