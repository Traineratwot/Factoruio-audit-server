import { Tag } from "primereact/tag";
import type React from "react";
import { CATEGORY_COLORS } from "@/constants/categories";
import type { Mod } from "@/types/mod";

export const CategoryColumn: React.FC<{ rowData: Mod }> = ({ rowData }) => {
	const category = rowData.category || "Uncategorized";
	const color = CATEGORY_COLORS[category.toLowerCase()] || "#6b7280";

	return (
		<Tag
			value={category}
			style={{
				backgroundColor: color,
				color: "#fff",
				fontWeight: "500",
				borderRadius: "20px",
				padding: "0.25rem 0.75rem",
				fontSize: "0.75rem",
			}}
		/>
	);
};
