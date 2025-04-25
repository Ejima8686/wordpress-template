const plugin = require("tailwindcss/plugin");

const rem = (px) => `${px / 16}rem`;

const container = plugin(function ({ addComponents, theme }) {
	addComponents({
		".container": {
			paddingRight: "calc(20 / 375 * 100vw)",
			paddingLeft: "calc(20 / 375 * 100vw)",

			"@screen md": {
				paddingRight: "calc(121 / 1440 * 100vw)",
				paddingLeft: "calc(121 / 1440 * 100vw)",
				margin: "0 auto",
				maxWidth: theme("screens.2xl"),
			},
		},
	});
});

const kerning = plugin(function ({ addUtilities }) {
	addUtilities({
		".kerning": {
			fontKerning: "auto",
			fontFeatureSettings: "'palt'",
		},
		".not-kerning": {
			fontKerning: "none",
			fontFeatureSettings: "normal",
		},
	});
});

const typography = plugin(function ({ addComponents, addUtilities, theme }) {
	addComponents({
		".text-display": {
			fontSize: "6.5vw",
			fontWeight: 900,
			lineHeight: theme("lineHeight.180"),
			letterSpacing: theme("letterSpacing.wider"),
			"@screen md": {
				fontSize: `min(6vw,${theme("fontSize.60")})`,
				lineHeight: theme("lineHeight.150"),
			},
		},
	});

	addUtilities({
		".writing-vertical": {
			writingMode: "vertical-rl",
		},
	});
});

module.exports = {
	container,
	kerning,
	typography,
};
