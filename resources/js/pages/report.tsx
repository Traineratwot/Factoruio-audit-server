import React, { useState } from 'react';
import { Card } from 'primereact/card';
import { ProgressBar } from 'primereact/progressbar';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Tag } from 'primereact/tag';
import { Button } from 'primereact/button';
import type { Finding, rawReport } from '@/types/mod';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';


export function formatBytes(
    bytes: number,
    significance: 1 | 2 | 3 | 4 = 3,
): string {
    if (bytes < 1024) return `${bytes} B`;
    const units = ['B', 'kiB', 'MiB', 'GiB', 'TiB'];
    const exponent = Math.floor(Math.log(bytes) / Math.log(1024));
    const value = bytes / Math.pow(1024, exponent);
    const digits = Math.max(
        0,
        significance - Math.floor(Math.log10(value)) - 1,
    );
    return `${value.toFixed(digits)} ${units[exponent]}`;
}

// Helper: format date from timestamp
const formatDate = (timestamp: number): string => {
    return new Date(timestamp * 1000).toLocaleString();
};

// Component for paths cell with show more/less button
const PathsCell: React.FC<{ paths?: string[] }> = ({ paths }) => {
    const [showAll, setShowAll] = useState(false);
    if (!paths || paths.length === 0) return <span>—</span>;
    const DISPLAY_LIMIT = 3;
    const hasMore = paths.length > DISPLAY_LIMIT;
    const displayedPaths = showAll ? paths : paths.slice(0, DISPLAY_LIMIT);
    return (
        <div className="flex-column flex gap-1">
            {displayedPaths.map((p, idx) => (
                <code key={idx} className="rounded bg-gray-800 p-1 text-sm">
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

// Severity tag component
const SeverityTag: React.FC<{ severity?: string }> = ({ severity }) => {
    let severityMap: Record<
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

interface AuditReportViewerProps {
    report: {
        raw: rawReport;
    };
}

const AuditReportViewer: React.FC<AuditReportViewerProps> = ({ report }) => {
    const auditReport = report.raw.report;
    const modInfo = report.raw.modInfo;

    // Build mod portal link
    const modUrl = `https://mods.factorio.com/mod/${modInfo.name}`;
    const overallScore = auditReport.score;
    const overallFillColor =
        overallScore >= 70
            ? '#22c55e'
            : overallScore >= 40
              ? '#f16338'
              : '#ef4444';
    const potentialSavings = auditReport.potentialSavings || 0;
    const percentageSavings = auditReport.percentageSavings || 0;

    return (
        <div className="container mx-auto p-4" style={{ maxWidth: '960px' }}>
            {/* Header */}
            <div className="mb-4">
                <h1 className="text-2xl font-bold">
                    <a
                        href={modUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-inherit no-underline"
                    >
                        {auditReport.modNameReadable}
                    </a>
                    <span className="ml-2 text-gray-400">
                        v{auditReport.version}
                    </span>
                </h1>
                <div className="text-sm text-gray-400">
                    <code>{auditReport.modName}</code>
                    <br />
                    SHA1: <code>{auditReport.sha1}</code> ·{' '}
                    {formatDate(auditReport.timestamp)}
                </div>
            </div>

            {/* Stat Cards */}
            <div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card className="shadow-none">
                    <div className="text-xs text-gray-400 uppercase">
                        Overall Score
                    </div>
                    <div
                        className="text-3xl font-bold"
                        style={{ color: overallFillColor }}
                    >
                        {overallScore.toFixed(1)}
                    </div>
                    <div className="text-sm text-gray-400">/ 100</div>
                </Card>
                <Card className="shadow-none">
                    <div className="text-xs text-gray-400 uppercase">
                        Mod Size
                    </div>
                    <div className="text-3xl font-bold">
                        {formatBytes(auditReport.modSize ?? 0)}
                    </div>
                </Card>
                <Card className="shadow-none">
                    <div className="text-xs text-gray-400 uppercase">
                        Potential Savings
                    </div>
                    <div className="text-3xl font-bold">
                        {formatBytes(potentialSavings)}
                    </div>
                    <div className="text-sm text-gray-400">
                        {percentageSavings.toFixed(1)}% reduction
                    </div>
                </Card>
            </div>

            {/* Overall Score ProgressBar */}
            <div className="mb-6">
                <ProgressBar
                    value={overallScore}
                    showValue={false}
                    style={{ height: '8px' }}
                    color={overallFillColor}
                />
            </div>

            {/* Errors section if any */}
            {auditReport.errors && auditReport.errors.length > 0 && (
                <Card title="Errors" className="mb-4 border-1 border-red-500">
                    <ul className="list-disc pl-4 text-red-500">
                        {auditReport.errors.map((err, idx) => (
                            <li key={idx}>{err}</li>
                        ))}
                    </ul>
                </Card>
            )}

            {/* Preflight findings if any */}
            {auditReport.preflightFindings &&
                auditReport.preflightFindings.length > 0 && (
                    <Card title="Preflight Findings" className="mb-4">
                        <DataTable
                            value={auditReport.preflightFindings}
                            size="small"
                        >
                            <Column
                                field="severity"
                                header="Severity"
                                body={(row: Finding) => (
                                    <SeverityTag severity={row.severity} />
                                )}
                            />
                            <Column field="description" header="Description" />
                            <Column
                                field="potentialSavings"
                                header="Savings"
                                body={(row: Finding) => (
                                    <span className="text-green-500">
                                        {formatBytes(row.potentialSavings ?? 0)}
                                    </span>
                                )}
                            />
                            <Column
                                header="Paths"
                                body={(row: Finding) => (
                                    <PathsCell paths={row.paths} />
                                )}
                            />
                        </DataTable>
                    </Card>
                )}

            {/* Scanner Results */}
            <h2 className="mb-3 border-b pb-2 text-xl font-semibold">
                Scanner Results
            </h2>
            {auditReport.scanners.map((scanner) => {
                const scannerScoreColor =
                    scanner.score >= 70
                        ? '#22c55e'
                        : scanner.score >= 40
                          ? '#f16338'
                          : '#ef4444';
                return (
                    <Card key={scanner.id} className="mb-4 shadow-sm">
                        <div className="mb-2 flex flex-wrap items-start justify-between gap-2">
                            <h3 className="font-mono text-lg text-cyan-400">
                                {scanner.id}
                            </h3>
                            <div className="text-right">
                                <div
                                    className="text-xl font-bold"
                                    style={{ color: scannerScoreColor }}
                                >
                                    {scanner.score.toFixed(1)}
                                </div>
                                <div className="text-xs text-gray-400">
                                    / 100
                                </div>
                                <ProgressBar
                                    value={scanner.score}
                                    showValue={false}
                                    style={{ height: '4px', width: '120px' }}
                                    color={scannerScoreColor}
                                />
                            </div>
                        </div>
                        <div className="mb-3 flex gap-4 text-sm text-gray-400">
                            <span>
                                <strong>Weight:</strong> {scanner.weight}
                            </span>
                            <span>
                                <strong>Savings:</strong>{' '}
                                <span className="text-green-500">
                                    {formatBytes(scanner.savings)}
                                </span>
                            </span>
                        </div>
                        {scanner.findings.length === 0 ? (
                            <div className="text-sm text-green-500">
                                No findings
                            </div>
                        ) : (
                            <DataTable
                                value={scanner.findings}
                                size="small"
                                className="p-datatable-sm"
                            >
                                <Column
                                    header="Severity"
                                    body={(row: Finding) => (
                                        <SeverityTag severity={row.severity} />
                                    )}
                                />
                                <Column
                                    field="description"
                                    header="Description"
                                />
                                <Column
                                    header="Savings"
                                    body={(row: Finding) => (
                                        <span className="font-semibold text-green-500">
                                            {formatBytes(row.potentialSavings ?? 0)}
                                        </span>
                                    )}
                                />
                                <Column
                                    header="Paths"
                                    body={(row: Finding) => (
                                        <PathsCell paths={row.paths} />
                                    )}
                                />
                            </DataTable>
                        )}
                    </Card>
                );
            })}
        </div>
    );
};

export default AuditReportViewer;
