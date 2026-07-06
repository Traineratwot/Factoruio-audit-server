import inertia from "@inertiajs/vite";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import { defineConfig } from "vite";

export default defineConfig({
	server: {
		host: "0.0.0.0", // слушать все интерфейсы внутри контейнера
		port: 5273,
		strictPort: true, // не выбирать другой порт, если 5173 занят
		hmr: {
			host: "localhost", // домен для HMR, соответствует APP_URL
			protocol: "ws", // WebSocket для HMR
			clientPort: 5273, // порт для HMR-клиента
		},
		watch: {
			// usePolling: true // важно для Docker — polling вместо native watchers
		},
	},
	plugins: [
		laravel({
			input: ["resources/css/app.css", "resources/js/app.tsx"],
			refresh: true,
			fonts: [
				bunny("Instrument Sans", {
					weights: [400, 500, 600],
				}),
			],
		}),
		inertia({
			ssr: {
				entry: "resources/js/ssr.tsx",
			},
		}),
		react({
			babel: {
				plugins: ["babel-plugin-react-compiler"],
			},
		}),
		tailwindcss(),
	],
});
