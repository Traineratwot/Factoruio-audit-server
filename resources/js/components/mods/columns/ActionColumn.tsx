import { router } from '@inertiajs/react';
import { Button } from 'primereact/button';
import React from 'react';
import type { Mod } from '@/types/mod';

interface ActionColumnProps {
    rowData: Mod;
    onAuditClick: (mod: Mod) => void;
}

export const ActionColumn: React.FC<ActionColumnProps> = ({
    rowData,
    onAuditClick,
}) => {
    if (rowData.reports_count === 0) {
        return (
            <Button
                label="Audit"
                icon="pi pi-play"
                size="small"
                onClick={() => onAuditClick(rowData)}
                severity="warning"
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
    }

    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
            <Button
                label="Report"
                icon="pi pi-chart-line"
                size="small"
                onClick={() => router.visit(rowData.report_url)}
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
            {rowData.latest_report_version &&
                rowData.latest_version &&
                rowData.latest_report_version !== rowData.latest_version && (
                    <i
                        className="pi pi-exclamation-triangle"
                        style={{
                            fontSize: '0.85rem',
                            color: '#f59e0b',
                            cursor: 'help',
                        }}
                        title={`Report is for v${rowData.latest_report_version}, latest is v${rowData.latest_version}`}
                    />
                )}
        </div>
    );
};
