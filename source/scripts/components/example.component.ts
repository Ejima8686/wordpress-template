declare const __THEME__: string;

import Alpine from "alpinejs";

Alpine.data("example", () => {
	return {
		init() {
			if (import.meta.env.MODE !== "production") {
				console.log(__THEME__ + " is develop mode");
			}
		},
	};
});
