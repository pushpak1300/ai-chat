<script setup lang="ts">
import hljs from 'highlight.js'
import VueMarkdown from 'vue-markdown-render'

interface Props {
  content?: string
}

const props = defineProps<Props>()

const markdownOptions = {
  html: true,
  breaks: true,
  linkify: true,
  typographer: true,
  highlight(str: string, lang: string) {
    if (lang && hljs.getLanguage(lang)) {
      return hljs.highlight(str, { language: lang }).value
    }
    return `<pre><code class="hljs">${str}</code></pre>`
  },
}
</script>

<template>
  <div
    v-if="props.content"
    class="prose prose-zinc dark:prose-invert max-w-none min-w-0 overflow-hidden break-words prose-p:m-0 prose-code:font-mono prose-pre:border prose-pre:border-border prose-pre:rounded-md prose-pre:p-4 prose-pre:mb-1 prose-pre:bg-foreground prose-pre:dark:bg-background"
  >
    <VueMarkdown
      :source="props.content"
      :options="markdownOptions"
      @ready="(md: any) => md.linkify.set({ fuzzyEmail: false })"
    />
  </div>
</template>
