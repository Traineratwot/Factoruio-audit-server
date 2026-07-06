import { UploadIcon } from "primereact/icons/upload";
import { ProgressBar } from "primereact/progressbar";
import type React from "react";
import type { Mod } from "@/types/mod";

interface ScoreColumnProps {
	rowData: Mod;
	onAuditClick: (mod: Mod) => void;
}

export const ScoreColumn: React.FC<ScoreColumnProps> = ({
	rowData,
	onAuditClick,
}) => {
	const score = rowData.score;
	const isOutdated =
		rowData.latest_report_version !== null &&
		rowData.latest_version !== null &&
		rowData.latest_report_version !== rowData.latest_version;

	if (score === null || score === undefined || score === 0) {
		return <span style={{ color: "#6b7280" }}>—</span>;
	}

	const color = score >= 70 ? "#22c55e" : score >= 40 ? "#f16338" : "#ef4444";

	return (
		<div style={{ display: "flex", alignItems: "center", gap: "0.5rem" }}>
			<span style={{ fontWeight: "bold", color, minWidth: "2.5rem" }}>
				{score.toFixed(1)}
			</span>
			<ProgressBar
				value={score}
				showValue={false}
				style={{ height: "6px", width: "4rem" }}
				color={color}
			/>
			{isOutdated && (
				<button
					type="button"
					onClick={() => onAuditClick(rowData)}
					title={`Audit latest version (${rowData.latest_version})`}
					style={{
						width: "1.2rem",
						height: "1.2rem",
						borderRadius: "50%",
						background: "#f59e0b",
						padding: "3px",
						border: "none",
						display: "flex",
						alignItems: "center",
						justifyContent: "center",
						cursor: "pointer",
						flexShrink: 0,
					}}
				>
					<UploadIcon color={"black"}></UploadIcon>
				</button>
			)}
		</div>
	);
};
