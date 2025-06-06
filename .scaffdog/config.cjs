const { loadEnv } = require("vite");
const path = require("path");

const env = loadEnv(
	process.env.NODE_ENV || "development",
	path.resolve(process.cwd(), ".devcontainer"),
	"",
);

module.exports = {
	files: ["*"],
	variables: {
		theme: env.VITE_THEME_NAME,
	},
};
