import { useCallback, useState } from "react";
import type { ModSearchResult } from "@/types/mod";

interface UseAuditDialogReturn {
	visible: boolean;
	preselectedMod: ModSearchResult | null;
	preselectedVersion: string | null;
	openForMod: (mod: { id: number; name: string; title: string | null }) => void;
	openForModVersion: (
		mod: { id: number; name: string; title: string | null },
		version: string,
	) => void;
	openNew: () => void;
	close: () => void;
}

export const useAuditDialog = (): UseAuditDialogReturn => {
	const [visible, setVisible] = useState(false);
	const [preselectedMod, setPreselectedMod] = useState<ModSearchResult | null>(
		null,
	);
	const [preselectedVersion, setPreselectedVersion] = useState<string | null>(
		null,
	);

	const openForMod = useCallback(
		(mod: { id: number; name: string; title: string | null }) => {
			setPreselectedMod({ id: mod.id, name: mod.name, title: mod.name });
			setPreselectedVersion(null);
			setVisible(true);
		},
		[],
	);

	const openForModVersion = useCallback(
		(
			mod: { id: number; name: string; title: string | null },
			version: string,
		) => {
			setPreselectedMod({ id: mod.id, name: mod.name, title: mod.name });
			setPreselectedVersion(version);
			setVisible(true);
		},
		[],
	);

	const openNew = useCallback(() => {
		setPreselectedMod(null);
		setPreselectedVersion(null);
		setVisible(true);
	}, []);

	const close = useCallback(() => {
		setVisible(false);
		setPreselectedMod(null);
		setPreselectedVersion(null);
	}, []);

	return {
		visible,
		preselectedMod,
		preselectedVersion,
		openForMod,
		openForModVersion,
		openNew,
		close,
	};
};
