// utils/formatters.ts
export function formatBytes(
	bytes: number,
	significance: 1 | 2 | 3 | 4 = 3,
): string {
	if (bytes < 1024) {
		return `${bytes} B`;
	}

	const units = ["B", "kiB", "MiB", "GiB", "TiB"];
	const exponent = Math.floor(Math.log(bytes) / Math.log(1024));
	const value = bytes / 1024 ** exponent;
	const digits = Math.max(0, significance - Math.floor(Math.log10(value)) - 1);

	return `${value.toFixed(digits)} ${units[exponent]}`;
}

export const formatDate = (timestamp: number): string => {
	return new Date(timestamp * 1000).toLocaleString();
};
