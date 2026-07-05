// components/AuditReport/PathsCell.tsx
import { Button } from 'primereact/button';
import React, { useState } from 'react';

interface PathsCellProps {
    paths?: string[];
}

export const PathsCell: React.FC<PathsCellProps> = ({ paths }) => {
    const [showAll, setShowAll] = useState(false);

    if (!paths || paths.length === 0) {
        return <span>—</span>;
    }

    const DISPLAY_LIMIT = 3;
    const hasMore = paths.length > DISPLAY_LIMIT;
    const displayedPaths = showAll ? paths : paths.slice(0, DISPLAY_LIMIT);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
            {displayedPaths.map((p, idx) => (
                <code key={idx} className="rounded bg-gray-800 text-sm">
                    {p}
                </code>
            ))}
            {hasMore && (
                <Button
                    label={
                        showAll
                            ? 'Show less'
                            : `+${paths.length - DISPLAY_LIMIT} more`
                    }
                    onClick={() => setShowAll(!showAll)}
                    className="p-button-text p-button-sm"
                    style={{ justifyContent: 'flex-start', paddingLeft: 0 }}
                />
            )}
        </div>
    );
};
