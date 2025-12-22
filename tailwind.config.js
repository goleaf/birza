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

    ],
}
