import { Button } from "primereact/button";
import { IconField } from "primereact/iconfield";
import { InputIcon } from "primereact/inputicon";
import { InputText } from "primereact/inputtext";
import type React from "react";

interface TableHeaderProps {
	searchQuery: string;
	onSearchChange: (value: string) => void;
	onClearSearch: () => void;
	totalRecords: number;
	onAuditClick: () => void;
}

export const TableHeader: React.FC<TableHeaderProps> = ({
	searchQuery,
	onSearchChange,
	onClearSearch,
	totalRecords,
	onAuditClick,
}) => {
	return (
		<div
			style={{
				display: "flex",
				flexWrap: "wrap",
				alignItems: "center",
				justifyContent: "space-between",
				gap: "1rem",
				padding: "0.5rem 0 1.5rem 0",
			}}
		>
			<div
				style={{
					display: "flex",
					alignItems: "center",
					gap: "1rem",
					flex: 1,
					minWidth: 0,
				}}
			>
				<div style={{ flex: 1, maxWidth: "20rem" }}>
					<IconField iconPosition="left">
						<InputIcon className="pi pi-search" />
						<InputText
							value={searchQuery}
							onChange={(e) => onSearchChange(e.target.value)}
							placeholder="Search mods..."
							style={{
								width: "100%",
								borderRadius: "30px",
								paddingLeft: "2.5rem",
								background: "#1f2937",
								borderColor: "#374151",
								color: "#e5e7eb",
							}}
						/>
					</IconField>
				</div>
				{searchQuery && (
					<Button
						icon="pi pi-times"
						severity="secondary"
						outlined
						onClick={onClearSearch}
						size="small"
						style={{ borderRadius: "30px" }}
					/>
				)}
			</div>
			<div style={{ display: "flex", alignItems: "center", gap: "1rem" }}>
				<div
					className="hidden sm:block"
					style={{
						color: "#9ca3af",
						fontSize: "0.9rem",
						background: "#1f2937",
						padding: "0.3rem 1rem",
						borderRadius: "30px",
						border: "1px solid #374151",
					}}
				>
					<i className="pi pi-database" style={{ marginRight: "0.5rem" }} />
					{totalRecords} mods
				</div>
				<Button
					icon="pi pi-play"
					size="small"
					onClick={onAuditClick}
					style={{ borderRadius: "30px" }}
					className="hidden sm:inline-flex"
				>
					Audit mod
				</Button>
			</div>
		</div>
	);
};
