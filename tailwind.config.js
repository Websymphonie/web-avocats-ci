/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './templates/**/*.twig',
        './assets/**/*.{js,jsx,ts,tsx,scss,css}',
        './src/**/*.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
