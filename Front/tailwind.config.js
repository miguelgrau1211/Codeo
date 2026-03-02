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
      spacing: {
        '112': '28rem',
      },
      screens: {
        'xs': '400px',
      },
      colors: {
        'editor-bg': '#282C34',
        'sidebar-bg': '#21252B',
        'ui-bg': '#333842',
        'primary-bg': 'var(--primary-bg)',
        'secondary-bg': 'var(--secondary-bg)',
        'accent-color': 'var(--accent-color)',
        'text-main': 'var(--text-main)',
        'text-muted': 'var(--text-muted)',
        'editor-surface': 'var(--editor-surface, #020617)',
        'terminal-surface': 'var(--terminal-surface, var(--secondary-bg))',
        'terminal-header': 'var(--terminal-header, rgba(0,0,0,0.4))',
      },
      animation: {
        blob: "blob 7s infinite",
        "fade-in-up": "fadeInUp 0.5s ease-out forwards",
        "spin-slow": "spin-slow 20s linear infinite",
        "zoom-pulse": "zoom-pulse 15s ease-in-out infinite",
        "slide-in-right": "slideInRight 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards",
        "celestial-glow": "celestialGlow 3s infinite alternate",
      },
      keyframes: {
        slideInRight: {
          "from": { transform: "translateX(100%)", opacity: "0" },
          "to": { transform: "translateX(0)", opacity: "1" },
        },
        celestialGlow: {
          "from": { "box-shadow": "0 0 10px rgba(239, 68, 68, 0.2)" },
          "to": { "box-shadow": "0 0 30px rgba(239, 68, 68, 0.4)" },
        },
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
