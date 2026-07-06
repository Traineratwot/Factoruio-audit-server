import type React from "react";
import type { Mod } from "@/types/mod";
import { formatDate } from "@/utils/format";

export const DateColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
	return (
		<div style={{ fontSize: "0.85rem", color: "#d1d5db" }}>
			{formatDate(rowData.created_at)}
		</div>
	);
};
