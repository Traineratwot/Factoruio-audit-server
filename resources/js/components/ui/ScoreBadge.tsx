import { ProgressBar } from "primereact/progressbar";
import type React from "react";
import { getScoreColor } from "@/utils/score";

interface ScoreBadgeProps {
	score: number;
	size?: "sm" | "md";
	showBar?: boolean;
}

export const ScoreBadge: React.FC<ScoreBadgeProps> = ({
	score,
	size = "sm",
	showBar = true,
}) => {
	const color = getScoreColor(score);
	const fontSize = size === "sm" ? "0.875rem" : "1.875rem";
	const barHeight = size === "sm" ? "6px" : "8px";
	const barWidth = size === "sm" ? "4rem" : "100%";

	return (
		<div style={{ display: "flex", alignItems: "center", gap: "0.5rem" }}>
			<span style={{ fontWeight: "bold", color, fontSize, minWidth: "2.5rem" }}>
				{score.toFixed(1)}
			</span>
			{showBar && (
				<ProgressBar
					value={score}
					showValue={false}
					style={{ height: barHeight, width: barWidth }}
					color={color}
				/>
			)}
		</div>
	);
};
