import { createInertiaApp } from "@inertiajs/react";
import createServer from "@inertiajs/react/server";
import ReactDOMServer from "react-dom/server";

// Inertia v3 SSR: calling createInertiaApp without page/render returns a render function.
// The type defs don't cover this overload — see node_modules/@inertiajs/react/types/createInertiaApp.d.ts:42
const render = (await createInertiaApp({
	// @ts-expect-error resolve/setup types mismatch Inertia's generic overloads
	resolve: (name: string) => {
		const pages = import.meta.glob("./pages/**/*.tsx");
		return pages[`./pages/${name}.tsx`]();
	},
	setup: ({ App, props }) => <App {...props} />,
})) as unknown as (
	page: Parameters<Parameters<typeof createServer>[0]>[0],
	renderToString: typeof ReactDOMServer.renderToString,
) => Promise<{ head: string[]; body: string }>;

createServer((page) => render(page, ReactDOMServer.renderToString));
