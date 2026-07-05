// components/AuditReport/SeverityTag.tsx
import { Tag } from 'primereact/tag';
import React from 'react';

interface SeverityTagProps {
    severity?: string;
}

export const SeverityTag: React.FC<SeverityTagProps> = ({ severity }) => {
    const severityMap: Record<
        string,
        {
            severity: 'success' | 'info' | 'warning' | 'danger' | undefined;
            label: string;
        }
    > = {
        high: { severity: 'danger', label: 'HIGH' },
        medium: { severity: 'warning', label: 'MEDIUM' },
        low: { severity: 'success', label: 'LOW' },
        info: { severity: 'info', label: 'INFO' },
    };
    const key = severity?.toLowerCase() || 'info';
    const mapped = severityMap[key] || severityMap.info;

    return <Tag severity={mapped.severity} value={mapped.label} />;
};
