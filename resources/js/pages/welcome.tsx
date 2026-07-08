import { Head, router, usePage } from "@inertiajs/react";
import { PrimeReactProvider } from "primereact/api";
import { Toast } from "primereact/toast";
import "primereact/resources/themes/lara-dark-cyan/theme.css";
import { Tooltip } from "primereact/tooltip";
import { useEffect, useRef, useState } from "react";
import { AuditDialog } from "@/components/mods/AuditDialog";
import { CategoryFilter } from "@/components/mods/CategoryFilter";
import { ModsTable } from "@/components/mods/ModsTable";
import { ReportFilter } from "@/components/mods/ReportFilter";
import Container from "@/components/ui/Container";
import Footer from "@/components/ui/Footer";
import echo from "@/echo";
import { useModsFilter } from "@/hooks/useModsFilter";
import type {
	Mod,
	ModSearchResult,
	PaginatedMods,
	ReportFilterValue,
} from "@/types/mod";

interface WelcomeProps {
	mods: PaginatedMods;
	search: string;
	categoryInclude?: string[];
	categoryExclude?: string[];
	category_all: string[];
	sort_field: string;
	sort_direction: string;
	report_filter: ReportFilterValue;
	factorio_version?: string;
	factorio_versions?: string[];
}

export default function Welcome({
	mods,
	search,
	categoryInclude = [],
	categoryExclude = [],
	category_all = [],
	sort_field = "created_at",
	sort_direction = "desc",
	report_filter = "all",
	factorio_version = "",
}: WelcomeProps) {
	const { audit_token } = usePage().props as unknown as { audit_token: string };
	const [auditDialogVisible, setAuditDialogVisible] = useState(false);
	const [auditMod, setAuditMod] = useState<ModSearchResult | null>(null);
	const toastRef = useRef<Toast>(null);

	useEffect(() => {
		if (!echo) return;
		const echoClient = echo;
		const channel = echoClient.channel(`audit.${audit_token}`);

		channel.listen(
			".AuditCompleted",
			(e: {
				mod_name: string;
				version: string;
				report_url: string | null;
				error: string | null;
			}) => {
				if (e.error) {
					toastRef.current?.show({
						severity: "error",
						summary: "Audit Failed",
						detail: `Failed to audit ${e.mod_name} v${e.version}: ${e.error}`,
						life: 10000,
					});
				} else {
					toastRef.current?.show({
						severity: "success",
						summary: "Audit Complete",
						detail: `${e.mod_name} v${e.version} audit finished!`,
						life: 10000,
					});
					if (e.report_url) {
						router.visit(e.report_url);
					}
				}
			},
		);

		return () => {
			echoClient.leaveChannel(`audit.${audit_token}`);
		};
	}, [audit_token]);

	const {
		searchQuery,
		setSearchQuery,
		categoryFilter,
		toggleCategory,
		resetFilters,
		loading,
		handlePageChange,
		clearSearch,
		sortField,
		sortDirection,
		handleSort,
		reportFilter,
		handleReportFilterChange,
	} = useModsFilter(
		search,
		categoryInclude,
		categoryExclude,
		sort_field,
		sort_direction,
		report_filter,
		factorio_version,
	);

	const allCategories = Array.from(new Set(category_all)).sort();

	const handleAuditMod = (mod: Mod) => {
		setAuditMod({ id: mod.id, name: mod.name, title: mod.name });
		setAuditDialogVisible(true);
	};

	const handleAuditNew = () => {
		setAuditMod(null);
		setAuditDialogVisible(true);
	};

	return (
		<>
			<Head title="Mods | Overview">
				<meta
					name="description"
					content="Explore Factorio mods with automated audit reports. Find performance issues, security concerns, and optimization opportunities."
				/>
				<meta property="og:title" content="Mods Catalog | Factorio-Audit" />
				<meta
					property="og:description"
					content="Explore Factorio mods with automated audit reports. Find performance issues, security concerns, and optimization opportunities."
				/>
				<meta property="og:type" content="website" />

				<meta name="twitter:card" content="summary" />
				<meta name="twitter:title" content="Mods Catalog | Factorio-Audit" />
				<meta
					name="twitter:description"
					content="Explore Factorio mods with automated audit reports. Find performance issues, security concerns, and optimization opportunities."
				/>
			</Head>
			<PrimeReactProvider>
				<Toast ref={toastRef} position="bottom-right" />
				<Tooltip target=".custom-tooltip" />
				<Container maxWidth="120rem" padding="1.5rem" className="min-h-screen">
					{/* Header */}
					<div
						style={{
							textAlign: "center",
							marginBottom: "2rem",
						}}
					>
						<h1
							style={{
								fontSize: "2.5rem",
								fontWeight: "bold",
								marginBottom: "0.5rem",
								background: "linear-gradient(135deg, #06b6d4, #3b82f6)",
								WebkitBackgroundClip: "text",
								backgroundClip: "text",
								color: "transparent",
								textShadow: "0 0 20px rgba(6,182,212,0.3)",
							}}
						>
							Mods Catalog
						</h1>
						<p style={{ color: "#9ca3af", fontSize: "1.1rem" }}>
							Explore popular mods and their reports
						</p>
					</div>

					{/* Main layout */}
					<div
						style={{
							display: "flex",
							gap: "2rem",
							alignItems: "flex-start",
						}}
					>
						<div
							style={{
								width: "250px",
								flexShrink: 0,
								display: "flex",
								flexDirection: "column",
								gap: "1rem",
							}}
						>
							<ReportFilter
								reportFilter={reportFilter}
								onFilterChange={handleReportFilterChange}
							/>
							{/*<FactorioVersionFilter*/}
							{/*    versions={factorio_versions}*/}
							{/*    selectedVersion={factorioVersion}*/}
							{/*    onVersionChange={handleFactorioVersionChange}*/}
							{/*/>*/}
							<CategoryFilter
								categories={allCategories}
								categoryFilter={categoryFilter}
								onToggleCategory={toggleCategory}
								onReset={resetFilters}
							/>
						</div>
						<div style={{ flex: 1 }}>
							<ModsTable
								mods={mods}
								loading={loading}
								searchQuery={searchQuery}
								onSearchChange={setSearchQuery}
								onClearSearch={clearSearch}
								onPageChange={handlePageChange}
								sortField={sortField}
								sortDirection={sortDirection}
								onSortChange={handleSort}
								onAuditClick={handleAuditNew}
								onAuditMod={handleAuditMod}
							/>
						</div>
					</div>
				</Container>
				<Footer />
				<AuditDialog
					visible={auditDialogVisible}
					onHide={() => {
						setAuditDialogVisible(false);
						setAuditMod(null);
					}}
					preselectedMod={auditMod}
				/>
				\
			</PrimeReactProvider>
		</>
	);
}
