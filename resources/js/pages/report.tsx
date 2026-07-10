import { Head, router, usePage } from "@inertiajs/react";
import { PrimeReactProvider } from "primereact/api";
import { Button } from "primereact/button";
import { Card } from "primereact/card";
import { Column } from "primereact/column";
import { DataTable } from "primereact/datatable";
import { Dropdown } from "primereact/dropdown";
import { ProgressBar } from "primereact/progressbar";
import { Toast } from "primereact/toast";
import type React from "react";
import { useCallback, useRef } from "react";
import "primereact/resources/themes/lara-dark-cyan/theme.css";
import "primeicons/primeicons.css";
import { ChevronLeftIcon } from "primereact/icons/chevronleft";
import { PathsCell, SeverityTag } from "@/components/AuditReport";
import { AuditDialog } from "@/components/mods/AuditDialog";
import Container from "@/components/ui/Container";
import Footer from "@/components/ui/Footer";
import { ModAvatar } from "@/components/ui/ModAvatar";
import { ScoreBadge } from "@/components/ui/ScoreBadge";
import { useAuditDialog } from "@/hooks/useAuditDialog";
import { useAuditEcho } from "@/hooks/useAuditEcho";
import type { Finding, rawReport } from "@/types/mod";
import { formatBytes, formatDate } from "@/utils/formatters";
import { getScoreColor } from "@/utils/score";

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
		summary: string | null;
		category: string | null;
	};
	versions: ReportVersion[];
	current_version: string;
	latest_version: string | null;
	reported_versions: string[];
	current_scanner_version: string | null;
}

const AuditReportViewer: React.FC<AuditReportViewerProps> = ({
	report,
	mod,
	versions,
	current_version,
	latest_version,
	reported_versions,
	current_scanner_version,
}) => {
	const { audit_token } = usePage().props as unknown as { audit_token: string };
	const toastRef = useRef<Toast>(null);
	const {
		visible,
		preselectedMod,
		preselectedVersion,
		openForModVersion,
		close,
	} = useAuditDialog();

	const handleAuditSuccess = useCallback((reportUrl: string) => {
		router.visit(reportUrl);
	}, []);

	useAuditEcho(audit_token, { toastRef, onSuccess: handleAuditSuccess });

	const hasReport = (v: string) => reported_versions.includes(v);

	const handleVersionChange = (e: { value: string }) => {
		if (hasReport(e.value)) {
			router.get(
				`/report/mod/${mod.name}/version/${e.value}`,
				{},
				{ preserveState: true, replace: true },
			);
		} else {
			openForModVersion(mod, e.value);
		}
	};

	const dropdownItemTemplate = (option: { label: string; value: string }) => {
		const has = hasReport(option.value);

		return (
			<div
				style={{
					display: "flex",
					alignItems: "center",
					gap: "0.5rem",
				}}
			>
				<i
					className={has ? "pi pi-check-circle" : "pi pi-times-circle"}
					style={{
						fontSize: "0.75rem",
						color: has ? "#22c55e" : "#6b7280",
					}}
				/>
				<span
					style={{
						color: has ? "#e5e7eb" : "#6b7280",
					}}
				>
					{option.label}
				</span>
			</div>
		);
	};

	const dropdownValueTemplate = (
		option: {
			label: string;
			value: string;
		} | null,
	) => {
		if (!option) {
			return <span>Select version...</span>;
		}

		const has = hasReport(option.value);

		return (
			<div
				style={{
					display: "flex",
					alignItems: "center",
					gap: "0.5rem",
				}}
			>
				<i
					className={has ? "pi pi-check-circle" : "pi pi-times-circle"}
					style={{
						fontSize: "0.75rem",
						color: has ? "#22c55e" : "#f59e0b",
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

	const _selectedOption =
		versionOptions.find((o) => o.value === current_version) ?? null;

	const isOutdated =
		report !== null &&
		latest_version !== null &&
		current_version !== latest_version &&
		!reported_versions.includes(latest_version);

	const isScannerOutdated =
		report !== null &&
		current_scanner_version !== null &&
		report.raw.report.scannerVersion < parseInt(current_scanner_version, 10);

	const ModHeader = () => (
		<div className="mb-4 flex flex-col sm:flex-row items-center sm:items-center gap-4">
			<ModAvatar
				image={mod.image}
				name={mod.title || mod.name}
				size="lg"
				shape="square"
			/>
			<div className="flex-1 text-center sm:text-left">
				<h1 className="text-xl sm:text-2xl font-bold">
					<a
						href={`https://mods.factorio.com/mod/${mod.name}`}
						target="_blank"
						rel="noopener"
					>
						{report ? report.raw.report.modNameReadable : mod.title || mod.name}
					</a>
				</h1>
				<div className="text-sm text-gray-400">
					<code>{mod.name}</code>
					{report && (
						<>
							<br />
							SHA1: <code>{report.raw.report.sha1}</code> ·{" "}
							{formatDate(report.raw.report.timestamp)}
						</>
					)}
				</div>
			</div>
			<button
				type="button"
				onClick={() => router.get("/")}
				aria-label="Back to mods"
				style={{
					flexShrink: 0,
					width: "2.5rem",
					height: "2.5rem",
					borderRadius: "50%",
					background: "#3f4b5b",
					border: "none",
					display: "flex",
					alignItems: "center",
					justifyContent: "center",
					cursor: "pointer",
				}}
			>
				<ChevronLeftIcon />
			</button>
		</div>
	);

	if (!report) {
		return (
			<PrimeReactProvider>
				<Head title={`${mod.title || mod.name} | Report`}>
					<meta
						name="description"
						content={
							mod.summary ||
							`Audit report for Factorio mod ${mod.title || mod.name}`
						}
					/>
					<meta
						property="og:title"
						content={`${mod.title || mod.name} | Factorio-Audit`}
					/>
					<meta
						property="og:description"
						content={
							mod.summary ||
							`Audit report for Factorio mod ${mod.title || mod.name}`
						}
					/>
					<meta property="og:type" content="article" />

					{mod.image && (
						<>
							<meta property="og:image" content={mod.image} />
							<meta property="og:image:height" content="144" />
							<meta property="og:image:width" content="144" />
						</>
					)}
				</Head>
				<Container maxWidth={960} padding="1rem">
					<ModHeader />

					<Card className="mb-4">
						<div className="mb-3">
							<label
								htmlFor="report-version-dropdown-no-report"
								className="mb-1 block text-sm font-medium text-gray-300"
							>
								Version
							</label>
							<Dropdown
								inputId="report-version-dropdown-no-report"
								value={current_version}
								options={versionOptions}
								onChange={handleVersionChange}
								valueTemplate={dropdownValueTemplate}
								itemTemplate={dropdownItemTemplate}
								style={{ width: "100%" }}
							/>
						</div>

						<div className="text-center text-gray-400">
							<p className="mb-4">
								No audit report for version {current_version}.
							</p>
							<Button
								label={`Audit v${current_version}`}
								icon="pi pi-play"
								size="large"
								severity="info"
								raised
								onClick={() => {
									openForModVersion(mod, current_version);
								}}
								style={{
									borderRadius: "24px",
									padding: "0.75rem 2rem",
									fontSize: "1.1rem",
								}}
							/>
						</div>
					</Card>

					<AuditDialog
						visible={visible}
						onHide={close}
						preselectedMod={preselectedMod}
						preselectedVersion={preselectedVersion}
					/>
				</Container>
				<Footer />
			</PrimeReactProvider>
		);
	}

	const auditReport = report.raw.report;
	const _modUrl = `https://mods.factorio.com/mod/${mod.name}`;
	const overallScore = auditReport.score;
	const overallFillColor = getScoreColor(overallScore);
	const potentialSavings = auditReport.potentialSavings || 0;
	const percentageSavings = auditReport.percentageSavings || 0;

	return (
		<PrimeReactProvider>
			<Toast ref={toastRef} position="bottom-right" />
			<Head
				title={`${auditReport.modNameReadable} v${auditReport.version} | Report`}
			>
				<meta
					name="description"
					content={`Audit score: ${auditReport.score.toFixed(1)}/100. ${mod.summary || ""}`.trim()}
				/>
				<meta
					property="og:title"
					content={`${auditReport.modNameReadable} v${auditReport.version} | Factorio-Audit`}
				/>
				<meta
					property="og:description"
					content={`Audit score: ${auditReport.score.toFixed(1)}/100. ${mod.summary || ""}`.trim()}
				/>
				<meta property="og:type" content="article" />

				{mod.image && (
					<>
						<meta property="og:image" content={mod.image} />
						<meta property="og:image:height" content="144" />
						<meta property="og:image:width" content="144" />
					</>
				)}
				<script type="application/ld+json">
					{JSON.stringify({
						"@context": "https://schema.org",
						"@type": "SoftwareApplication",
						name: auditReport.modNameReadable,
						applicationCategory: "GameApplication",
						operatingSystem: "Factorio",
						description: mod.summary || undefined,
						image: mod.image || undefined,
						url: `https://mods.factorio.com/mod/${mod.name}`,
						version: auditReport.version,
						offers: { "@type": "Offer", price: "0", priceCurrency: "USD" },
						review: {
							"@type": "Review",
							reviewRating: {
								"@type": "Rating",
								ratingValue: auditReport.score.toFixed(1),
								bestRating: "100",
							},
							author: { "@type": "Organization", name: "Factorio-Audit" },
						},
					})}
				</script>
			</Head>
			<Container maxWidth={960} padding="1rem">
				<ModHeader />

				{/* Version selector */}
				<div className="mb-4">
					<label
						htmlFor="report-version-dropdown"
						className="mb-1 block text-sm font-medium text-gray-300"
					>
						Version
					</label>
					<Dropdown
						inputId="report-version-dropdown"
						value={current_version}
						options={versionOptions}
						onChange={handleVersionChange}
						valueTemplate={dropdownValueTemplate}
						itemTemplate={dropdownItemTemplate}
						style={{ width: "100%" }}
					/>
				</div>

				{/* Outdated report warning */}
				{isOutdated && (
					<div
						className="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-lg border border-amber-500/40 px-4 py-3"
						style={{ background: "rgba(245, 158, 11, 0.08)" }}
					>
						<div className="flex items-center gap-2">
							<i
								className="pi pi-exclamation-triangle"
								style={{ color: "#f59e0b" }}
							/>
							<span className="text-sm text-amber-200">
								This report is for version <strong>{current_version}</strong>,
								but the latest version is <strong>{latest_version}</strong>.
							</span>
						</div>
						<Button
							label={`Audit v${latest_version}`}
							icon="pi pi-play"
							size="small"
							severity="warning"
							text
							raised
							onClick={() => {
								if (latest_version) {
									openForModVersion(mod, latest_version);
								}
							}}
							style={{
								borderRadius: "20px",
								padding: "0.25rem 1rem",
								whiteSpace: "nowrap",
								alignSelf: "flex-end",
							}}
						/>
					</div>
				)}

				{/* Outdated scanner warning */}
				{isScannerOutdated && (
					<div
						className="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-lg border border-amber-500/40 px-4 py-3"
						style={{ background: "rgba(245, 158, 11, 0.08)" }}
					>
						<div className="flex items-center gap-2">
							<i
								className="pi pi-exclamation-triangle"
								style={{ color: "#f59e0b" }}
							/>
							<span className="text-sm text-amber-200">
								This report was made with scanner{" "}
								<strong>v{report.raw.report.scannerVersion}</strong>, current
								version is <strong>v{current_scanner_version}</strong>.
							</span>
						</div>
						<Button
							label="Re-audit"
							icon="pi pi-refresh"
							size="small"
							severity="warning"
							text
							raised
							onClick={() => {
								openForModVersion(mod, current_version);
							}}
							style={{
								borderRadius: "20px",
								padding: "0.25rem 1rem",
								whiteSpace: "nowrap",
								alignSelf: "flex-end",
							}}
						/>
					</div>
				)}

				{/* Stat Cards */}
				<div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
					<Card className="shadow-none">
						<div className="text-xs text-gray-400 uppercase">Overall Score</div>
						<div
							className="text-3xl font-bold"
							style={{ color: overallFillColor }}
						>
							{overallScore.toFixed(1)}
						</div>
						<div className="text-sm text-gray-400">/ 100</div>
					</Card>
					<Card className="shadow-none">
						<div className="text-xs text-gray-400 uppercase">Mod Size</div>
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
						style={{ height: "8px" }}
						color={overallFillColor}
					/>
				</div>

				{/* Errors section if any */}
				{auditReport.errors && auditReport.errors.length > 0 && (
					<Card title="Errors" className="mb-4 border border-red-500">
						<ul className="list-disc pl-4 text-red-500">
							{auditReport.errors.map((err) => (
								<li key={err}>{err}</li>
							))}
						</ul>
					</Card>
				)}

				{/* Preflight findings if any */}
				{auditReport.preflightFindings &&
					auditReport.preflightFindings.length > 0 && (
						<Card title="Preflight Findings" className="mb-4">
							<DataTable value={auditReport.preflightFindings} size="small">
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
									body={(row: Finding) => <PathsCell paths={row.paths} />}
								/>
							</DataTable>
						</Card>
					)}

				{/* Scanner Results */}
				<h2 className="mb-3 border-b pb-2 text-xl font-semibold">
					Scanner Results{" "}
					<sup style={{ color: "#666" }}>
						{`v ${auditReport.scannerVersion}`}
					</sup>
				</h2>
				{auditReport.scanners.map((scanner) => {
					return (
						<Card key={scanner.id} className="mb-4 shadow-sm">
							<div className="mb-2 flex flex-wrap items-start justify-between gap-2">
								<h3 className="font-mono text-lg text-cyan-400">
									{scanner.id}
								</h3>
								<div className="text-right">
									<ScoreBadge score={scanner.score} size="md" showBar={false} />
									<div className="text-xs text-gray-400">/ 100</div>
									<ProgressBar
										value={scanner.score}
										showValue={false}
										style={{
											height: "4px",
											width: "120px",
										}}
										color={getScoreColor(scanner.score)}
									/>
								</div>
							</div>
							<div className="mb-3 flex gap-4 text-sm text-gray-400">
								<span>
									<strong>Weight:</strong> {scanner.weight}
								</span>
								<span>
									<strong>Savings:</strong>{" "}
									<span className="text-green-500">
										{formatBytes(scanner.savings)}
									</span>
								</span>
							</div>
							{scanner.findings.length === 0 ? (
								<div className="text-sm text-green-500">No findings</div>
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
									<Column field="description" header="Description" />
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
										body={(row: Finding) => <PathsCell paths={row.paths} />}
									/>
								</DataTable>
							)}
						</Card>
					);
				})}
			</Container>

			<Footer />
			<AuditDialog
				visible={visible}
				onHide={close}
				preselectedMod={preselectedMod}
				preselectedVersion={preselectedVersion}
			/>
		</PrimeReactProvider>
	);
};

export default AuditReportViewer;
