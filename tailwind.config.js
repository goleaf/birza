const colors = require('tailwindcss/colors')

module.exports = {
    content: [
        "./resources/**/*.blade.php",
        // WireUI classes live across the package PHP + TS sources.
        "./vendor/wireui/wireui/src/**/*.php",
        "./vendor/wireui/wireui/ts/**/*.{ts,js}",
    ],

    theme: {
        extend: {
            colors: {
                primary: colors.indigo,
                secondary: colors.gray,
                positive: colors.emerald,
                negative: colors.red,
                warning: colors.amber,
                info: colors.blue
            },
        },
    },

    presets: [
        require("./vendor/wireui/wireui/tailwind.config.js")
    ],

    plugins: [
        require('flowbite/plugin'),
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
        require('daisyui'),
    ],
    daisyui: {
        themes: [
            {
                admin: {
                    primary: "#0f766e",
                    secondary: "#0f172a",
                    accent: "#f97316",
                    neutral: "#1f2937",
                    "base-100": "#ffffff",
                    "base-200": "#f6f4ef",
                    "base-300": "#e7e2d6",
                    info: "#0ea5e9",
                    success: "#16a34a",
                    warning: "#f59e0b",
                    error: "#dc2626",
                },
            },
            "corporate",
            "light",
        ],
    },
}
