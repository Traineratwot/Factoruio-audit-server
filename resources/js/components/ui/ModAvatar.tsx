import type React from "react";

interface ModAvatarProps {
	image?: string | null;
	name: string;
	size?: "sm" | "lg";
	shape?: "circle" | "square";
}

const sizeMap = {
	sm: { width: "2.5rem", height: "2.5rem", fontSize: "1rem" },
	lg: { width: "4rem", height: "4rem", fontSize: "1.5rem" },
};

const shapeMap = {
	circle: "50%",
	square: "8px",
};

export const ModAvatar: React.FC<ModAvatarProps> = ({
	image,
	name,
	size = "sm",
	shape = "circle",
}) => {
	const s = sizeMap[size];
	const radius = shapeMap[shape];

	if (image) {
		return (
			<img
				src={image}
				alt={name}
				style={{
					width: s.width,
					height: s.height,
					borderRadius: radius,
					objectFit: "cover",
					flexShrink: 0,
				}}
			/>
		);
	}

	return (
		<div
			style={{
				width: s.width,
				height: s.height,
				borderRadius: radius,
				background: "linear-gradient(135deg, #06b6d4, #3b82f6)",
				display: "flex",
				alignItems: "center",
				justifyContent: "center",
				color: "#fff",
				fontWeight: "bold",
				fontSize: s.fontSize,
				flexShrink: 0,
			}}
		>
			{name.charAt(0).toUpperCase()}
		</div>
	);
};
