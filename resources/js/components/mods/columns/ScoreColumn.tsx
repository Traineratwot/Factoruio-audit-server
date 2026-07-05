import { ProgressBar } from 'primereact/progressbar';
import React from 'react';
import type { Mod } from '@/types/mod';

export const ScoreColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
    const score = rowData.score;

    if (score === null || score === undefined) {
        return <span style={{ color: '#6b7280' }}>—</span>;
    }

    const color = score >= 70 ? '#22c55e' : score >= 40 ? '#f16338' : '#ef4444';

    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <span style={{ fontWeight: 'bold', color, minWidth: '2.5rem' }}>
                {score.toFixed(1)}
            </span>
            <ProgressBar
                value={score}
                showValue={false}
                style={{ height: '6px', width: '4rem' }}
                color={color}
            />
        </div>
    );
};
