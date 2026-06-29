/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#eef4ff',
          100: '#dbe7ff',
          200: '#bcd2ff',
          300: '#8eb3ff',
          400: '#5a8aff',
          500: '#3865f5',
          600: '#2647d6',
          700: '#2038ad',
          800: '#1f328a',
          900: '#1f2f6e',
        },
      },
    },
  },
  plugins: [],
}
