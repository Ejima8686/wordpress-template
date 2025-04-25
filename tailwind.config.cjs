const glob = require("glob");
const { container, kerning, typography } = require("./tailwind-plugins.cjs");

require("dotenv").config({ path: ".devcontainer/.env" });

const rem = (px) => `${px / 16}rem`;

const THEME_NAME = process.env.THEME_NAME;
// https://github.com/WebDevStudios/wd_s/pull/804#issuecomment-997018146
const topLevelPhpFiles = glob.sync(`./${THEME_NAME}/*.php`);

/** @type {import('tailwindcss/types').Config} */
module.exports = {
  content: [
    ...topLevelPhpFiles,
    `./${THEME_NAME}/inc/**/*.php`,
    "./source/**/*.{js,ts}",
  ],
  future: {
    hoverOnlyWhenSupported: true,
  },
  theme: {
    screens: {
      ip: "375px",
      sm: "640px",
      md: "768px",
      lg: "1024px",
      xl: "1280px",
      "2xl": "1440px",
    },
    extend: {
      aria: {
        invalid: 'invalid="true"',
      },
      colors: {
        black: "#000000",
        white: "#ffffff",
      },
      fontFamily: {
        sans: ["sans-serif"],
        serif: ["serif"],
      },
      spacing: {
        7.5: rem(30),
        22: rem(88),
        30: rem(120),
        40: rem(160),
      },
      fontSize: {
        ...[8, 10, 12, 13, 14, 15, 17, 20, 24, 28, 40, 60].reduce((previousValue, currentValue) => {
          previousValue[currentValue] = rem(currentValue);
          return previousValue;
        }, {}),
      },
      lineHeight: {
        ...[100, 130, 150, 160, 170, 180, 200].reduce((previousValue, currentValue) => {
          previousValue[currentValue] = `${currentValue}%`;
          return previousValue;
        }, {}),
      },
      letterSpacing: {
        ...["01", "04", "05", "06", "07", "09", "11", "12", "18"].reduce(
          (previousValue, currentValue) => {
            previousValue[currentValue] = `0.${currentValue}em`;
            return previousValue;
          },
          {},
        ),
      },
      transitionTimingFunction: {
        "sine-in": "cubic-bezier(0.12, 0, 0.39, 0)",
        "sine-out": "cubic-bezier(0.61, 1, 0.88, 1)",
        "sine-in-out": "cubic-bezier(0.37, 0, 0.63, 1)",
        "quad-in": "cubic-bezier(0.11, 0, 0.5, 0)",
        "quad-out": "cubic-bezier(0.5, 1, 0.89, 1)",
        "quad-in-out": "cubic-bezier(0.45, 0, 0.55, 1)",
        "cubic-in": "cubic-bezier(0.32, 0, 0.67, 0)",
        "cubic-out": "cubic-bezier(0.33, 1, 0.68, 1)",
        "cubic-in-out": "cubic-bezier(0.65, 0, 0.35, 1)",
        "quart-in": "cubic-bezier(0.5, 0, 0.75, 0)",
        "quart-out": "cubic-bezier(0.25, 1, 0.5, 1)",
        "quart-in-out": "cubic-bezier(0.76, 0, 0.24, 1)",
        "quint-in": "cubic-bezier(0.64, 0, 0.78, 0)",
        "quint-out": "cubic-bezier(0.22, 1, 0.36, 1)",
        "quint-in-out": "cubic-bezier(0.83, 0, 0.17, 1)",
        "expo-in": "cubic-bezier(0.7, 0, 0.84, 0)",
        "expo-out": "cubic-bezier(0.16, 1, 0.3, 1)",
        "expo-in-out": "cubic-bezier(0.87, 0, 0.13, 1)",
        "circ-in": "cubic-bezier(0.55, 0, 1, 0.45)",
        "circ-out": "cubic-bezier(0, 0.55, 0.45, 1)",
        "circ-in-out": "cubic-bezier(0.85, 0, 0.15, 1)",
        "back-in": "cubic-bezier(0.36, 0, 0.66, -0.56)",
        "back-out": "cubic-bezier(0.34, 1.56, 0.64, 1)",
        "back-in-out": "cubic-bezier(0.68, -0.6, 0.32, 1.6)",
      },
    },
  },
  corePlugins: {
    container: false,
  },
  plugins: [container, kerning, typography],
};
