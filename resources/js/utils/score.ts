export const getScoreColor = (score: number): string =>
	score >= 70 ? "#22c55e" : score >= 40 ? "#f16338" : "#ef4444";
