import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
	interface Window {
		Pusher: typeof Pusher;
	}
}

if (typeof window !== "undefined") {
	window.Pusher = Pusher;
}

function createEcho() {
	if (typeof window === "undefined") return undefined;
	return new Echo({
		broadcaster: "reverb",
		key: import.meta.env.VITE_REVERB_APP_KEY,
		wsHost: import.meta.env.VITE_REVERB_HOST,
		wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
		wssPort: Number(import.meta.env.VITE_REVERB_PORT) || 443,
		forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
		enabledTransports: ["ws", "wss"],
	});
}

const echo = createEcho();

export default echo;
