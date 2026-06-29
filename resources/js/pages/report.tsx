import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card } from 'primereact/card';
import { ProgressBar } from 'primereact/progressbar';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Dropdown } from 'primereact/dropdown';
import { PrimeReactProvider } from 'primereact/api';
import 'primereact/resources/themes/lara-dark-cyan/theme.css';
import type { Finding, rawReport } from '@/types/mod';
import { formatBytes, formatDate } from '@/utils/formatters';
import { PathsCell, SeverityTag } from '@/components/AuditReport';
import Container from '@/components/ui/Container';
import { AuditDialog } from '@/components/mods/AuditDialog';

interface ReportVersion {
    id: number;
    version: string;
    factorio_version: string;
    released_at: string;
}

interface AuditReportViewerProps {
    report: {
        raw: rawReport;
    } | null;
    mod: {
        id: number;
        name: string;
        title: string | null;
        image: string | null;
    };
    versions: ReportVersion[];
    current_version: string;
    reported_versions: string[];
}

const AuditReportViewer: React.FC<AuditReportViewerProps> = ({
    report,
    mod,
    versions,
    current_version,
    reported_versions,
}) => {
    const [auditDialogVisible, setAuditDialogVisible] = useState(false);
    const [auditVersion, setAuditVersion] = useState<string | null>(null);

    const hasReport = (v: string) => reported_versions.includes(v);

    const handleVersionChange = (e: { value: string }) => {
        if (hasReport(e.value)) {
            router.get(
                `/report/mod/${mod.name}/version/${e.value}`,
                {},
                { preserveState: true, replace: true },
            );
        } else {
            setAuditVersion(e.value);
            setAuditDialogVisible(true);
        }
    };

    const dropdownItemTemplate = (option: {
        label: string;
        value: string;
    }) => {
        const has = hasReport(option.value);
        return (
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem',
                }}
            >
                <i
                    className={has ? 'pi pi-check-circle' : 'pi pi-times-circle'}
                    style={{
                        fontSize: '0.75rem',
                        color: has ? '#22c55e' : '#6b7280',
                    }}
                />
                <span
                    style={{
                        color: has ? '#e5e7eb' : '#6b7280',
                    }}
                >
                    {option.label}
                </span>
            </div>
        );
    };

    const dropdownValueTemplate = (option: {
        label: string;
        value: string;
    } | null) => {
        if (!option) {
            return <span>Select version...</span>;
        }
        const has = hasReport(option.value);
        return (
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem',
                }}
            >
                <i
                    className={has ? 'pi pi-check-circle' : 'pi pi-times-circle'}
                    style={{
                        fontSize: '0.75rem',
                        color: has ? '#22c55e' : '#f59e0b',
                    }}
                />
                <span>{option.label}</span>
            </div>
        );
    };

    const versionOptions = versions.map((v) => ({
        label: `${v.version} (Factorio ${v.factorio_version})`,
        value: v.version,
    }));

    const selectedOption =
        versionOptions.find((o) => o.value === current_version) ?? null;

    const ModHeader = () => (
        <div className="mb-4 flex items-center gap-4">
            {mod.image ? (
                <img
                    src={mod.image}
                    alt={mod.name}
                    style={{
                        width: '4rem',
                        height: '4rem',
                        borderRadius: '8px',
                        objectFit: 'cover',
                    }}
                />
            ) : (
                <div
                    style={{
                        width: '4rem',
                        height: '4rem',
                        borderRadius: '8px',
                        background:
                            'linear-gradient(135deg, #06b6d4, #3b82f6)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: '#fff',
                        fontWeight: 'bold',
                        fontSize: '1.5rem',
                    }}
                >
                    {(mod.title || mod.name).charAt(0).toUpperCase()}
                </div>
            )}
            <div>
                <h1 className="text-2xl font-bold">
                    {report
                        ? report.raw.report.modNameReadable
                        : mod.title || mod.name}
                </h1>
                <div className="text-sm text-gray-400">
                    <code>{mod.name}</code>
                    {report && (
                        <>
                            <br />
                            SHA1: <code>{report.raw.report.sha1}</code> ·{' '}
                            {formatDate(report.raw.report.timestamp)}
                        </>
                    )}
                </div>
            </div>
        </div>
    );

    if (!report) {
        return (
            <PrimeReactProvider>
                <Head title={`${mod.title || mod.name} | Report`} />
                <Container maxWidth={960} padding="1rem">
                    <ModHeader />

                    <Card className="mb-4">
                        <div className="mb-3">
                            <label className="mb-1 block text-sm font-medium text-gray-300">
                                Version
                            </label>
                            <Dropdown
                                value={current_version}
                                options={versionOptions}
                                onChange={handleVersionChange}
                                valueTemplate={dropdownValueTemplate}
                                itemTemplate={dropdownItemTemplate}
                                style={{ width: '100%' }}
                            />
                        </div>

                        <div className="text-center text-gray-400">
                            <p className="mb-3">
                                No audit report for version {current_version}.
                            </p>
                        </div>
                    </Card>

                    <AuditDialog
                        visible={auditDialogVisible}
                        onHide={() => {
                            setAuditDialogVisible(false);
                            setAuditVersion(null);
                        }}
                        preselectedMod={{
                            id: mod.id,
                            name: mod.name,
                            title: mod.title || mod.name,
                        }}
                        preselectedVersion={auditVersion}
                    />
                </Container>
            </PrimeReactProvider>
        );
    }

    const auditReport = report.raw.report;
    const modUrl = `https://mods.factorio.com/mod/${mod.name}`;
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
        <PrimeReactProvider>
            <Head
                title={`${auditReport.modNameReadable} v${auditReport.version} | Report`}
            />
            <Container maxWidth={960} padding="1rem">
                <ModHeader />

                {/* Version selector */}
                <div className="mb-4">
                    <label className="mb-1 block text-sm font-medium text-gray-300">
                        Version
                    </label>
                    <Dropdown
                        value={current_version}
                        options={versionOptions}
                        onChange={handleVersionChange}
                        valueTemplate={dropdownValueTemplate}
                        itemTemplate={dropdownItemTemplate}
                        style={{ width: '100%' }}
                    />
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
                    <Card
                        title="Errors"
                        className="mb-4 border border-red-500"
                    >
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
                                <Column
                                    field="description"
                                    header="Description"
                                />
                                <Column
                                    field="potentialSavings"
                                    header="Savings"
                                    body={(row: Finding) => (
                                        <span className="text-green-500">
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
                        </Card>
                    )}

                {/* Scanner Results */}
                <h2 className="mb-3 border-b pb-2 text-xl font-semibold">
                    Scanner Results{' '}
                    <sup style={{ color: '#666' }}>
                        {`v ${auditReport.scannerVersion}`}
                    </sup>
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
                                        style={{
                                            height: '4px',
                                            width: '120px',
                                        }}
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
                                            <SeverityTag
                                                severity={row.severity}
                                            />
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

            <AuditDialog
                visible={auditDialogVisible}
                onHide={() => {
                    setAuditDialogVisible(false);
                    setAuditVersion(null);
                }}
                preselectedMod={{
                    id: mod.id,
                    name: mod.name,
                    title: mod.title || mod.name,
                }}
                preselectedVersion={auditVersion}
            />
        </PrimeReactProvider>
    );
};

export default AuditReportViewer;
