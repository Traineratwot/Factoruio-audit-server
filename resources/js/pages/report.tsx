// pages/report.tsx
import React from 'react';
import { Card } from 'primereact/card';
import { ProgressBar } from 'primereact/progressbar';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import type { Finding, rawReport } from '@/types/mod';
import { formatBytes, formatDate } from '@/utils/formatters';
import { PathsCell, SeverityTag } from '@/components/AuditReport';
import Container from '@/components/ui/Container';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';
import { Badge } from 'primereact/badge';
import { Tag } from 'primereact/tag';

interface AuditReportViewerProps {

    report: {
        raw: rawReport;
    };
}

const AuditReportViewer: React.FC<AuditReportViewerProps> = ({ report }) => {
    const auditReport = report.raw.report;
    const modInfo = report.raw.modInfo;

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
        <Container maxWidth={960} padding="1rem">
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
                <Card title="Errors" className="mb-4 border border-red-500">
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
                Scanner Results{' '}
                <sup style={{ color: '#666' }}>{`v ${auditReport.scannerVersion}`}</sup>
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
                                            {formatBytes(
                                                row.potentialSavings ?? 0,
                                            )}
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
        </Container>
    );
};

export default AuditReportViewer;
