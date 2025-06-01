<template>
  <div v-if="props.content" class="prose dark:prose-invert max-w-none min-w-0 overflow-hidden break-words" v-html="renderedContent" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { marked } from 'marked'

interface Props {
  content?: string
}

const props = defineProps<Props>()

const renderedContent = computed(() => {
  if (!props.content) {
    return ''
  }

  const html = marked(props.content, {
    gfm: true,
    breaks: true
  }) as string

  return html
    .replace(/<pre><code([^>]*)>/g, '<div class="not-prose flex flex-col max-w-full overflow-hidden"><pre class="text-sm w-full max-w-full overflow-x-auto dark:bg-zinc-900 p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl dark:text-zinc-50 text-zinc-900"><code class="whitespace-pre-wrap break-all word-break-break-all">')
    .replace(/<\/code><\/pre>/g, '</code></pre></div>')
    .replace(/<code([^>]*)>/g, '<code class="text-sm bg-zinc-100 dark:bg-zinc-800 py-0.5 px-1 rounded-md break-words word-break-break-all">')
    .replace(/<ol>/g, '<ol class="list-decimal list-outside ml-4 break-words">')
    .replace(/<ul>/g, '<ul class="list-decimal list-outside ml-4 break-words">')
    .replace(/<li>/g, '<li class="py-1 break-words">')
    .replace(/<strong>/g, '<span class="font-semibold break-words">')
    .replace(/<\/strong>/g, '</span>')
    .replace(/<a href="([^"]*)"([^>]*)>/g, '<a href="$1" class="text-blue-500 hover:underline break-words word-break-break-all" target="_blank" rel="noreferrer"$2>')
    .replace(/<h1([^>]*)>/g, '<h1 class="text-3xl font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<h2([^>]*)>/g, '<h2 class="text-2xl font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<h3([^>]*)>/g, '<h3 class="text-xl font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<h4([^>]*)>/g, '<h4 class="text-lg font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<h5([^>]*)>/g, '<h5 class="text-base font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<h6([^>]*)>/g, '<h6 class="text-sm font-semibold mt-6 mb-2 break-words"$1>')
    .replace(/<p([^>]*)>/g, '<p class="break-words"$1>')
});
</script>
