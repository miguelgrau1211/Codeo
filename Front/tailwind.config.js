/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        display: ['Space Grotesk', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6', // Primary Teal/Cyan
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        },
        accent: {
          purple: '#8b5cf6',
          violet: '#6366f1',
          pink: '#ec4899',
        },
        reward: {
          gold: '#fbbf24',
          yellow: '#f59e0b',
        },
        streak: {
          fire: '#f97316',
          orange: '#ea580c',
        },
        dark: {
          bg: '#030712', // Deepest gray/black
          surface: '#0f172a', // Slate 900
          border: '#1e293b', // Slate 800
        }
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-glow': 'conic-gradient(from 180deg at 50% 50%, #2dd4bf 0deg, #8b5cf6 180deg, #2dd4bf 360deg)',
        'shine': 'linear-gradient(45deg, transparent 25%, rgba(255,255,255,0.1) 50%, transparent 75%)',
      },
      animation: {
        'fade-in': 'fadeIn 0.6s ease-out forwards',
        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards', // Apple-like easing
        'float-slow': 'float 8s ease-in-out infinite',
        'float-fast': 'float 4s ease-in-out infinite',
        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'breath': 'breath 6s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(10px)' }, // Subtle movement
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        breath: {
          '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
          '50%': { opacity: '0.6', transform: 'scale(1.05)' },
        }
      }
    },
  },
  plugins: [
    // Simple plugin to add animation delays if not present
    function ({ addUtilities }) {
      const newUtilities = {
        '.delay-100': { 'animation-delay': '100ms' },
        '.delay-200': { 'animation-delay': '200ms' },
        '.delay-300': { 'animation-delay': '300ms' },
        '.delay-500': { 'animation-delay': '500ms' },
        '.delay-700': { 'animation-delay': '700ms' },
      }
      addUtilities(newUtilities)
    }
  ],
}
