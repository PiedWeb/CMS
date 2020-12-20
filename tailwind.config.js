module.exports = {
  purge: ['./src/Resources/view/*.html.twig', './src/Resources/assets/*.js'],
  theme: {
    extend: {
      typography: {
        DEFAULT: {
          css: {
            color: '#333',
            a: {
              color: 'var(--primary)',
              '&:hover': {
                color: 'var(--primary-light)',
              },
            },
          },
        },
      },
      colors: {
        primary: 'var(--primary)',
        'primary-light': 'var(--primary-light)',
        bg: 'var(--secondary)',
      },
    },
  },
  variants: {},
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
};
