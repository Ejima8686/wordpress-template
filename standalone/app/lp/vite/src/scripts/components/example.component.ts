import Alpine from "alpinejs";

Alpine.data("example", () => {
	return {
		init() {
			console.log("Remove this example component");
		},
		toggle() {
			this.show = !this.show;
		},
	};
});
