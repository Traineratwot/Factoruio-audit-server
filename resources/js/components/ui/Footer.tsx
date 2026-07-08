import Container from "@/components/ui/Container";

export default function Footer() {
	return (
		<footer
			style={{
				borderTop: "1px solid #374151",
				padding: "2rem 0",
				marginTop: "3rem",
			}}
		>
			<Container maxWidth="120rem">
				<div
					style={{
						display: "flex",
						flexWrap: "wrap",
						justifyContent: "space-between",
						gap: "2rem",
						fontSize: "0.875rem",
						color: "#9ca3af",
					}}
				>
					<div>
						<div style={{ fontWeight: 600, color: "#e5e7eb", marginBottom: "0.5rem" }}>
							Factorio-Audit
						</div>
						<p style={{ maxWidth: "28rem", lineHeight: 1.6 }}>
							Independent Factorio mod audit.
							<br />
							Not affiliated with Wube or Factorio.
							<br />
							Scores come from my tiny brain.
						</p>
					</div>
					{/*<div>*/}
					{/*	<div style={{ fontWeight: 600, color: "#e5e7eb", marginBottom: "0.5rem" }}>*/}
					{/*		Contact*/}
					{/*	</div>*/}
					{/*	<div style={{ display: "flex", flexDirection: "column", gap: "0.25rem" }}>*/}
					{/*		<a*/}
					{/*			href="https://github.com/aidan647"*/}
					{/*			target="_blank"*/}
					{/*			rel="noopener noreferrer"*/}
					{/*			style={{ color: "#06b6d4", textDecoration: "none" }}*/}
					{/*		>*/}
					{/*			<i className="pi pi-github" style={{ marginRight: "0.5rem" }} />*/}
					{/*			GitHub*/}
					{/*		</a>*/}
					{/*		<span>*/}
					{/*			<i className="pi pi-discord" style={{ marginRight: "0.5rem" }} />*/}
					{/*			aidan647 on Discord*/}
					{/*		</span>*/}
					{/*	</div>*/}
					{/*</div>*/}
				</div>
			</Container>
		</footer>
	);
}
