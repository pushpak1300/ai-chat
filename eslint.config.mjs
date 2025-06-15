import antfu from '@antfu/eslint-config'
import oxlint from 'eslint-plugin-oxlint'

export default antfu({
  vue: true,
  typescript: true,
  formatters: {
    css: true,
    html: true,
    markdown: 'prettier',
  },
  ignores: ['storage/**/*', '**/*.{yaml,yml,php}', 'resources/js/Components/shadcn/**/*', 'public/**/*'],
}, ...oxlint.buildFromOxlintConfigFile('./.oxlintrc.json'))
