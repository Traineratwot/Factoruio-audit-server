import { Button } from "primereact/button";
import { Dialog } from "primereact/dialog";
import type { DropdownChangeEvent } from "primereact/dropdown";
import { Dropdown } from "primereact/dropdown";
import { InputText } from "primereact/inputtext";
import { Message } from "primereact/message";
import { ProgressSpinner } from "primereact/progressspinner";
import type React from "react";
import { useEffect, useRef } from "react";
import { useAuditForm } from "@/hooks/useAuditForm";
import type { ModSearchResult } from "@/types/mod";

interface AuditDialogProps {
	visible: boolean;
	onHide: () => void;
	preselectedMod?: ModSearchResult | null;
	preselectedVersion?: string | null;
}

export const AuditDialog: React.FC<AuditDialogProps> = ({
	visible,
	onHide,
	preselectedMod = null,
	preselectedVersion = null,
}) => {
	const {
		searchQuery,
		searchResults,
		selectedMod,
		versions,
		selectedVersion,
		loading,
		loadingVersions,
		submitting,
		result,
		error,
		searchMods,
		selectMod,
		setVersion,
		submit,
		reset,
	} = useAuditForm();

	const preselectedRef = useRef<string | null>(null);

	useEffect(() => {
		if (
			visible &&
			preselectedMod &&
			preselectedRef.current !== preselectedMod.name
		) {
			preselectedRef.current = preselectedMod.name;
			selectMod(preselectedMod);
		}
	}, [visible, preselectedMod, selectMod]);

	useEffect(() => {
		if (visible && preselectedVersion && selectedMod && !loadingVersions) {
			setVersion(preselectedVersion);
		}
	}, [visible, preselectedVersion, selectedMod, loadingVersions, setVersion]);

	useEffect(() => {
		if (!visible) {
			reset();
			preselectedRef.current = null;
		}
	}, [visible, reset]);

	const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
		searchMods(e.target.value);
	};

	const handleVersionChange = (e: DropdownChangeEvent) => {
		setVersion(e.value);
	};

	const handleKeyDown = (e: React.KeyboardEvent) => {
		if (e.key === "Enter" && selectedMod && !submitting) {
			submit();
		}
	};

	const footer = (
		<div
			style={{
				display: "flex",
				justifyContent: "flex-end",
				gap: "0.5rem",
			}}
		>
			<Button
				label="Cancel"
				severity="secondary"
				outlined
				onClick={onHide}
				disabled={submitting}
			/>
			<Button
				label={submitting ? "Queuing..." : "Audit"}
				icon={submitting ? "pi pi-spin pi-spinner" : "pi pi-play"}
				onClick={submit}
				disabled={!selectedMod || submitting}
			/>
		</div>
	);

	return (
		<Dialog
			header="Audit Mod"
			visible={visible}
			onHide={onHide}
			footer={footer}
			style={{ width: "min(500px, 90vw)" }}
			modal
			closable={!submitting}
		>
			<div
				style={{
					display: "flex",
					flexDirection: "column",
					gap: "1rem",
				}}
			>
				{/* Search input */}
				<div>
					<label
						htmlFor="audit-search-input"
						style={{
							display: "block",
							marginBottom: "0.5rem",
							color: "#e5e7eb",
							fontWeight: "500",
						}}
					>
						Search mod
					</label>
					<div style={{ position: "relative" }}>
						<InputText
							id="audit-search-input"
							value={searchQuery}
							onChange={handleSearch}
							onKeyDown={handleKeyDown}
							placeholder="Type mod name..."
							disabled={submitting}
							style={{ width: "100%" }}
						/>
						{loading && (
							<div
								style={{
									position: "absolute",
									right: "0.75rem",
									top: "50%",
									transform: "translateY(-50%)",
								}}
							>
								<ProgressSpinner
									style={{
										width: "1.2rem",
										height: "1.2rem",
									}}
									strokeWidth="4"
								/>
							</div>
						)}
					</div>
				</div>

				{/* Search results */}
				{searchResults.length > 0 && !selectedMod && (
					<div
						style={{
							border: "1px solid #374151",
							borderRadius: "8px",
							maxHeight: "200px",
							overflowY: "auto",
						}}
					>
						{searchResults.map((mod) => (
							<button
								key={mod.id}
								type="button"
								onClick={() => selectMod(mod)}
								style={{
									padding: "0.5rem 0.75rem",
									cursor: "pointer",
									borderBottom: "1px solid #374151",
									color: "#e5e7eb",
									display: "block",
									width: "100%",
									textAlign: "left",
									background: "none",
									border: "none",
									borderBottomStyle: "solid",
									borderBottomWidth: "1px",
									borderBottomColor: "#374151",
								}}
								className="hover:bg-gray-700/50"
							>
								<div style={{ fontWeight: "500" }}>{mod.name}</div>
								<div
									style={{
										fontSize: "0.8rem",
										color: "#9ca3af",
									}}
								>
									{mod.title}
								</div>
							</button>
						))}
					</div>
				)}

				{/* Selected mod */}
				{selectedMod && (
					<div
						style={{
							padding: "0.75rem",
							background: "rgba(6,182,212,0.1)",
							border: "1px solid #06b6d4",
							borderRadius: "8px",
						}}
					>
						<div style={{ color: "#06b6d4", fontWeight: "500" }}>
							{selectedMod.name}
						</div>
						<div
							style={{
								fontSize: "0.8rem",
								color: "#9ca3af",
							}}
						>
							{selectedMod.title}
						</div>
					</div>
				)}

				{/* Version selector */}
				{selectedMod && (
					<div>
						<label
							htmlFor="audit-version-dropdown"
							style={{
								display: "block",
								marginBottom: "0.5rem",
								color: "#e5e7eb",
								fontWeight: "500",
							}}
						>
							Version
						</label>
						{loadingVersions ? (
							<div
								style={{
									display: "flex",
									alignItems: "center",
									gap: "0.5rem",
									padding: "0.75rem",
									border: "1px solid #374151",
									borderRadius: "6px",
									color: "#9ca3af",
								}}
							>
								<ProgressSpinner
									style={{
										width: "1.2rem",
										height: "1.2rem",
									}}
									strokeWidth="4"
								/>
								Loading versions...
							</div>
						) : (
							<Dropdown
								inputId="audit-version-dropdown"
								value={selectedVersion}
								options={versions.map((v) => ({
									label: `${v.version} (Factorio ${v.factorio_version})`,
									value: v.version,
								}))}
								onChange={handleVersionChange}
								style={{ width: "100%" }}
								disabled={submitting}
								placeholder="No versions available"
							/>
						)}
					</div>
				)}

				{/* Success message */}
				{result && (
					<Message
						severity="success"
						text={result.message}
						style={{ width: "100%" }}
					/>
				)}

				{/* Error message */}
				{error && (
					<Message severity="error" text={error} style={{ width: "100%" }} />
				)}
			</div>
		</Dialog>
	);
};
