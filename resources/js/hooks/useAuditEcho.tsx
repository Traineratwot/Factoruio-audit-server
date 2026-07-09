import type { RefObject } from "react";
import { useEffect } from "react";
import echo from "@/echo";

interface AuditCompletedEvent {
	mod_name: string;
	version: string;
	report_url: string | null;
	error: string | null;
}

interface UseAuditEchoOptions {
	toastRef?: RefObject<{
		show: (opts: Record<string, unknown>) => void;
	} | null>;
	onSuccess?: (reportUrl: string) => void;
}

export const useAuditEcho = (
	auditToken: string | undefined,
	{ toastRef, onSuccess }: UseAuditEchoOptions = {},
) => {
	useEffect(() => {
		if (!echo || !auditToken) return;

		const channel = echo.channel(`audit.${auditToken}`);

		channel.listen(".AuditCompleted", (e: AuditCompletedEvent) => {
			if (e.error) {
				toastRef?.current?.show({
					severity: "error",
					summary: "Audit Failed",
					detail: `Failed to audit ${e.mod_name} v${e.version}: ${e.error}`,
					life: 30000,
				});
			} else {
				const content = (
					<div
						style={{
							display: "flex",
							alignItems: "center",
							gap: "0.75rem",
							flexWrap: "wrap",
						}}
					>
						<span>
							{e.mod_name} v{e.version} audit finished!
						</span>
						{e.report_url && onSuccess && (
							<button
								type="button"
								onClick={() => {
									if (e.report_url) {
										onSuccess(e.report_url);
									}
								}}
								style={{
									background: "#06b6d4",
									color: "#fff",
									border: "none",
									borderRadius: "6px",
									padding: "0.25rem 0.75rem",
									cursor: "pointer",
									fontSize: "0.8rem",
									fontWeight: 500,
									whiteSpace: "nowrap",
								}}
							>
								View Report
							</button>
						)}
					</div>
				);
				toastRef?.current?.show({
					severity: "success",
					summary: "Audit Complete",
					content,
					life: 30000,
				});
			}
		});

		return () => {
			echo?.leaveChannel(`audit.${auditToken}`);
		};
	}, [auditToken, toastRef, onSuccess]);
};
