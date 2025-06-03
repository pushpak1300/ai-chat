import antfu from '@antfu/eslint-config'

export default antfu({
  vue: true,
  typescript: true,
  formatters: {
    css: true,
    html: true,
    markdown: 'prettier',
  },
  ignores: ['storage/**/*', '**/*.{yaml,yml,php}', 'resources/js/Components/shadcn/**/*', 'public/**/*'],
})
