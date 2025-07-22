import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import focus from "@alpinejs/focus";
import ui from "@alpinejs/ui";

if (import.meta.env.MODE !== "production") {
	console.log(import.meta.env);
}

(async () => {
	Alpine.plugin([collapse, focus, ui]);

	await import("./components");
	await import("./stores");

	(window as any).Alpine = Alpine;
	Alpine.start();
})();
