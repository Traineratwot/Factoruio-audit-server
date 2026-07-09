import { Head, router, usePage } from "@inertiajs/react";
import { PrimeReactProvider } from "primereact/api";
import { Toast } from "primereact/toast";
import "primereact/resources/themes/lara-dark-cyan/theme.css";
import { Tooltip } from "primereact/tooltip";
import { useCallback, useRef, useState } from "react";
import { AuditDialog } from "@/components/mods/AuditDialog";
import { CategoryFilter } from "@/components/mods/CategoryFilter";
import { ModsTable } from "@/components/mods/ModsTable";
import { ReportFilter } from "@/components/mods/ReportFilter";
import Container from "@/components/ui/Container";
import Footer from "@/components/ui/Footer";
import { useAuditDialog } from "@/hooks/useAuditDialog";
import { useAuditEcho } from "@/hooks/useAuditEcho";
import { useModsFilter } from "@/hooks/useModsFilter";
import type { PaginatedMods, ReportFilterValue } from "@/types/mod";

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
	const toastRef = useRef<Toast>(null);
	const { visible, preselectedMod, openForMod, openNew, close } =
		useAuditDialog();

	const handleAuditSuccess = useCallback((reportUrl: string) => {
		router.visit(reportUrl);
	}, []);

	useAuditEcho(audit_token, { toastRef, onSuccess: handleAuditSuccess });

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
	const [filtersOpen, setFiltersOpen] = useState(false);

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
				<Container maxWidth="120rem" padding="1rem" className="min-h-screen">
					{/* Header */}
					<div
						style={{
							textAlign: "center",
							marginBottom: "2rem",
						}}
					>
						<h1
							style={{
								fontSize: "clamp(1.5rem, 5vw, 2.5rem)",
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

					{/* Mobile filter toggle */}
					<div className="md:hidden" style={{ marginBottom: "1rem" }}>
						<button
							type="button"
							onClick={() => setFiltersOpen(!filtersOpen)}
							style={{
								display: "flex",
								alignItems: "center",
								gap: "0.5rem",
								padding: "0.5rem 1rem",
								borderRadius: "8px",
								border: "1px solid #374151",
								background: "#1f2937",
								color: "#e5e7eb",
								cursor: "pointer",
								fontSize: "0.9rem",
							}}
						>
							<i
								className={`pi ${filtersOpen ? "pi-chevron-up" : "pi-chevron-down"}`}
							/>
							Filters
						</button>
					</div>

					{/* Main layout */}
					<div
						style={{
							display: "flex",
							gap: "2rem",
							alignItems: "flex-start",
						}}
						className="flex-col md:flex-row"
					>
						<div
							style={{
								width: "250px",
								flexShrink: 0,
								display: "flex",
								flexDirection: "column",
								gap: "1rem",
							}}
							className={filtersOpen ? "block" : "hidden md:block"}
						>
							<ReportFilter
								reportFilter={reportFilter}
								onFilterChange={handleReportFilterChange}
							/>
							<CategoryFilter
								categories={allCategories}
								categoryFilter={categoryFilter}
								onToggleCategory={toggleCategory}
								onReset={resetFilters}
							/>
						</div>
						<div style={{ flex: 1, minWidth: 0 }}>
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
								onAuditClick={openNew}
								onAuditMod={(mod) => openForMod(mod)}
							/>
						</div>
					</div>
				</Container>
				<Footer />
				<AuditDialog
					visible={visible}
					onHide={close}
					preselectedMod={preselectedMod}
				/>
			</PrimeReactProvider>
		</>
	);
}
