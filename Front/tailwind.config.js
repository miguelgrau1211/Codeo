/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        jersey: ['"Jersey 10"', 'sans-serif'],
      },
      colors: {
        'editor-bg': '#282C34',
        'sidebar-bg': '#21252B',
        'ui-bg': '#333842',
      },
      animation: {
        blob: "blob 7s infinite",
        "fade-in-up": "fadeInUp 0.5s ease-out forwards",
        "spin-slow": "spin-slow 20s linear infinite",
        "zoom-pulse": "zoom-pulse 15s ease-in-out infinite",
      },
      keyframes: {
        blob: {
          "0%": {
            transform: "translate(0px, 0px) scale(1)",
          },
          "33%": {
            transform: "translate(30px, -50px) scale(1.1)",
          },
          "66%": {
            transform: "translate(-20px, 20px) scale(0.9)",
          },
          "100%": {
            transform: "translate(0px, 0px) scale(1)",
          },
        },
        fadeInUp: {
          "0%": {
            opacity: "0",
            transform: "translateY(20px)",
          },
          "100%": {
            opacity: "1",
            transform: "translateY(0)",
          },
        },
        "spin-slow": {
          "from": {
            transform: "rotate(0deg)",
          },
          "to": {
            transform: "rotate(360deg)",
          },
        },
        "zoom-pulse": {
          "0%, 100%": {
            transform: "scale(1)",
          },
          "50%": {
            transform: "scale(1.5)",
          },
        },
      },
    },
  },
  plugins: [],
}
