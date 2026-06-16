import React from 'react';
import { Button } from 'primereact/button';
import { router } from '@inertiajs/react';
import { Mod } from '@/types/mod';

export const ActionColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
    const handleClick = () => {
        router.visit(rowData.report_url);
    };
    return (
        <Button
            label="Report"
            icon="pi pi-chart-line"
            size="small"
            onClick={handleClick}
            severity="info"
            text
            raised
            style={{
                borderRadius: '20px',
                padding: '0.25rem 1rem',
                transition: 'all 0.2s',
            }}
            className="p-button-outlined"
        />
    );
};
