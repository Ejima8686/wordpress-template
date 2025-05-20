declare const __THEME__: string;

if (import.meta.env.MODE !== "production") {
	console.log(__THEME__ + " is develop mode");
}
