import { router } from "@inertiajs/react";
import type React from "react";
import type { Mod } from "@/types/mod";

export const NameColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
	return (
		<div style={{ display: "flex", alignItems: "center", gap: "0.75rem" }}>
			{rowData.image ? (
				<img
					src={rowData.image}
					alt={rowData.name}
					style={{
						width: "2.5rem",
						height: "2.5rem",
						borderRadius: "50%",
						objectFit: "cover",
						flexShrink: 0,
					}}
				/>
			) : (
				<div
					style={{
						width: "2.5rem",
						height: "2.5rem",
						borderRadius: "50%",
						background: "linear-gradient(135deg, #06b6d4, #3b82f6)",
						display: "flex",
						alignItems: "center",
						justifyContent: "center",
						color: "#fff",
						fontWeight: "bold",
						fontSize: "1rem",
						flexShrink: 0,
					}}
				>
					{(rowData.title || rowData.name).charAt(0).toUpperCase()}
				</div>
			)}
			<div>
				{rowData.reports_count > 0 ? (
					<button
						type="button"
						onClick={() => router.get(`/report/mod/${rowData.name}`)}
						style={{
							fontWeight: "600",
							color: "#06b6d4",
							background: "none",
							border: "none",
							padding: 0,
							cursor: "pointer",
							textDecoration: "underline",
							textDecorationColor: "rgba(6, 182, 212, 0.4)",
							textUnderlineOffset: "3px",
						}}
					>
						{rowData.title || rowData.name}
					</button>
				) : (
					<div style={{ fontWeight: "600", color: "#e5e7eb" }}>
						{rowData.title || rowData.name}
					</div>
				)}
				<div style={{ fontSize: "0.75rem", color: "#9ca3af" }}>
					{rowData.owner && `by ${rowData.owner}`}
					{rowData.latest_version && ` · v${rowData.latest_version}`}
				</div>
			</div>
		</div>
	);
};
