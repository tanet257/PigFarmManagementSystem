const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './app/Http/Livewire/**/*.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        prompt: ['Prompt', 'sans-serif'],
      },
      colors: {
        darkbg: "#1e1b29",     // ดำอมน้ำเงิน
        darkcard: "#2a2438",   // ดำอมม่วง
        purplemain: "#5a4e7c",
        purplehover: "#6a5ca1",
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
