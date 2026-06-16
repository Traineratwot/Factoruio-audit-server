import React from 'react';
import { Mod } from '@/types/mod';

export const DownloadsColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
    const count = rowData.downloads_count || 0;
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <i className="pi pi-download" style={{ color: '#60a5fa' }} />
            <span style={{ fontWeight: '500' }}>{count.toLocaleString()}</span>
        </div>
    );
};
