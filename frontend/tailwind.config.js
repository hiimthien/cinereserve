/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        cinema: {
          bg: '#0B0F19',
          surface: '#151C2C',
          card: '#1E293B',
          border: 'rgba(255, 255, 255, 0.08)',
          accent: '#E11D48',
          gold: '#F59E0B',
          text: '#F8FAFC',
          muted: '#94A3B8',
        },
        seat: {
          available: '#334155',
          'available-hover': '#475569',
          selected: '#10B981',
          holding: '#FBBF24',
          booked: '#450A0A',
          'booked-border': '#7F1D1D',
          vip: '#D97706',
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
      },
      boxShadow: {
        'glow-screen': '0 10px 40px -10px rgba(245, 158, 11, 0.3)',
        'glow-accent': '0 0 25px rgba(225, 29, 72, 0.4)',
        'glow-green': '0 0 20px rgba(16, 185, 129, 0.4)',
        'glow-gold': '0 0 20px rgba(251, 191, 36, 0.4)',
      }
    },
  },
  plugins: [],
}
