import { useCallback, useRef, useState } from "react";
import type { ModSearchResult, ModVersion } from "@/types/mod";

interface AuditFormState {
	searchQuery: string;
	searchResults: ModSearchResult[];
	selectedMod: ModSearchResult | null;
	versions: ModVersion[];
	selectedVersion: string | null;
	loading: boolean;
	loadingVersions: boolean;
	submitting: boolean;
	result: { success: boolean; message: string } | null;
	error: string | null;
}

export const useAuditForm = () => {
	const [state, setState] = useState<AuditFormState>({
		searchQuery: "",
		searchResults: [],
		selectedMod: null,
		versions: [],
		selectedVersion: null,
		loading: false,
		loadingVersions: false,
		submitting: false,
		result: null,
		error: null,
	});

	const abortRef = useRef<AbortController | null>(null);

	const searchMods = useCallback(async (query: string) => {
		if (!query || query.length < 2) {
			setState((s) => ({ ...s, searchQuery: query, searchResults: [] }));

			return;
		}

		abortRef.current?.abort();
		const controller = new AbortController();
		abortRef.current = controller;

		setState((s) => ({ ...s, searchQuery: query, loading: true }));

		try {
			const res = await fetch(
				`/api/mods/search?query=${encodeURIComponent(query)}&per_page=20`,
				{ signal: controller.signal },
			);
			const data = await res.json();
			const mods: ModSearchResult[] = (data.data ?? []).map(
				(m: { id: number | string; name: string; title?: string }) => ({
					id: Number(m.id),
					name: m.name,
					title: m.title ?? m.name,
				}),
			);
			setState((s) => ({ ...s, loading: false, searchResults: mods }));
		} catch {
			if (!controller.signal.aborted) {
				setState((s) => ({ ...s, loading: false }));
			}
		}
	}, []);

	const selectMod = useCallback(async (mod: ModSearchResult) => {
		setState((s) => ({
			...s,
			selectedMod: mod,
			searchQuery: mod.name,
			searchResults: [],
			versions: [],
			selectedVersion: null,
			loadingVersions: true,
			result: null,
			error: null,
		}));

		try {
			const res = await fetch(`/api/mods/${mod.id}/versions`);
			const data = await res.json();
			setState((s) => ({
				...s,
				versions: data.versions ?? [],
				selectedVersion: data.latest_version ?? null,
				loadingVersions: false,
			}));
		} catch {
			setState((s) => ({
				...s,
				versions: [],
				loadingVersions: false,
			}));
		}
	}, []);

	const setVersion = useCallback((version: string) => {
		setState((s) => ({ ...s, selectedVersion: version }));
	}, []);

	const submit = useCallback(async () => {
		if (!state.selectedMod) {
			return;
		}

		setState((s) => ({
			...s,
			submitting: true,
			result: null,
			error: null,
		}));

		try {
			const res = await fetch("/audit", {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					"X-Requested-With": "XMLHttpRequest",
					"X-XSRF-TOKEN": decodeURIComponent(
						document.cookie
							.split("; ")
							.find((c) => c.startsWith("XSRF-TOKEN="))
							?.split("=")[1] ?? "",
					),
				},
				body: JSON.stringify({
					mod_id: state.selectedMod.id,
					mod_version: state.selectedVersion,
				}),
			});

			const data = await res.json();

			if (res.ok) {
				setState((s) => ({
					...s,
					submitting: false,
					result: { success: true, message: data.message },
				}));
			} else {
				const msg =
					data.message ?? data.errors?.rate_limit?.[0] ?? "Audit failed";
				setState((s) => ({
					...s,
					submitting: false,
					error: msg,
				}));
			}
		} catch {
			setState((s) => ({
				...s,
				submitting: false,
				error: "Network error. Please try again.",
			}));
		}
	}, [state.selectedMod, state.selectedVersion]);

	const reset = useCallback(() => {
		setState({
			searchQuery: "",
			searchResults: [],
			selectedMod: null,
			versions: [],
			selectedVersion: null,
			loading: false,
			loadingVersions: false,
			submitting: false,
			result: null,
			error: null,
		});
	}, []);

	return {
		...state,
		searchMods,
		selectMod,
		setVersion,
		submit,
		reset,
	};
};
