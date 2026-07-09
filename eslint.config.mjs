export default [
  {
    ignores: [
      '.idea/**',
      '.webtolk/**',
      '.packages/**',
      '.phing/**',
      'logs/**',
      '**/*.min.js',
    ],
  },
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'script',
      globals: {
        Joomla: 'readonly',
        bootstrap: 'readonly',
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
      },
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': ['warn', { args: 'none' }],
    },
  },
];
