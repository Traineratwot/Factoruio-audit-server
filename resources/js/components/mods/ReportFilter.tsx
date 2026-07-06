import { Card } from "primereact/card";
import type React from "react";
import type { ReportFilterValue } from "@/types/mod";

interface ReportFilterProps {
	reportFilter: ReportFilterValue;
	onFilterChange: (value: ReportFilterValue) => void;
}

const options: {
	value: ReportFilterValue;
	label: string;
	icon: string;
	color: string;
}[] = [
	{ value: "all", label: "All", icon: "pi pi-list", color: "#6b7280" },
	{
		value: "with",
		label: "With report",
		icon: "pi pi-check-circle",
		color: "#22c55e",
	},
	{
		value: "without",
		label: "Without report",
		icon: "pi pi-times-circle",
		color: "#ef4444",
	},
];

export const ReportFilter: React.FC<ReportFilterProps> = ({
	reportFilter,
	onFilterChange,
}) => {
	return (
		<Card
			style={{
				border: "1px solid #374151",
				background: "rgba(31,41,55,0.6)",
				backdropFilter: "blur(8px)",
				borderRadius: "16px",
				padding: "1rem",
			}}
		>
			<h3
				style={{
					margin: "0 0 1rem 0",
					color: "#e5e7eb",
					fontSize: "1.1rem",
				}}
			>
				<i className="pi pi-filter" style={{ marginRight: "0.5rem" }} />
				Report status
			</h3>
			<div
				style={{
					display: "flex",
					flexDirection: "column",
					gap: "0.5rem",
				}}
			>
				{options.map((opt) => {
					const active = reportFilter === opt.value;

					return (
						<button
							key={opt.value}
							type="button"
							onClick={() => onFilterChange(opt.value)}
							style={{
								display: "flex",
								alignItems: "center",
								gap: "0.5rem",
								padding: "0.5rem 0.75rem",
								borderRadius: "8px",
								cursor: "pointer",
								backgroundColor: active ? `${opt.color}22` : "transparent",
								border: `1px solid ${active ? opt.color : "#374151"}`,
								transition: "all 0.2s",
								width: "100%",
								textAlign: "left",
								color: "inherit",
								font: "inherit",
							}}
							className="hover:bg-gray-700/30"
						>
							<i
								className={`pi ${opt.icon}`}
								style={{
									color: active ? opt.color : "#6b7280",
									fontSize: "0.9rem",
								}}
							/>
							<span style={{ color: "#e5e7eb", fontSize: "0.9rem" }}>
								{opt.label}
							</span>
						</button>
					);
				})}
			</div>
		</Card>
	);
};
