<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { AnimatePresence, motion } from 'motion-v'
import { ref } from 'vue'
import MarkdownRenderer from './MarkdownRenderer.vue'

interface Props {
  content: string
  isLoading: boolean
}

defineProps<Props>()

const isExpanded = ref(true)

const variants = {
  collapsed: {
    height: 0,
    opacity: 0,
    marginTop: 0,
    marginBottom: 0,
  },
  expanded: {
    height: 'auto',
    opacity: 1,
    marginTop: '1rem',
    marginBottom: '0.5rem',
  },
}
</script>

<template>
  <div class="flex flex-col">
    <div v-if="isLoading" class="flex flex-row gap-2 items-center">
      <div class="font-medium">
        Reasoning
      </div>
      <div class="animate-spin">
        <Icon icon="lucide:loader-2" class="size-4" />
      </div>
    </div>
    <div v-else class="flex flex-row gap-2 items-center">
      <div class="font-medium">
        Reasoned for a few seconds
      </div>
      <button
        v-if="content"
        data-testid="reasoning-toggle"
        type="button"
        class="cursor-pointer"
        @click="isExpanded = !isExpanded"
      >
        <Icon
          icon="lucide:chevron-down"
          class="size-4 transition-transform duration-200"
          :class="{ 'rotate-180': !isExpanded }"
        />
      </button>
    </div>

    <AnimatePresence :initial="false">
      <motion.div
        v-if="isExpanded && content"
        key="content"
        data-testid="reasoning-content"
        :initial="variants.collapsed"
        :animate="variants.expanded"
        :exit="variants.collapsed"
        :transition="{ duration: 0.2, ease: 'easeInOut' }"
        style="overflow: hidden"
        class="pl-4 text-zinc-600 dark:text-zinc-400 border-l flex flex-col gap-4"
      >
        <MarkdownRenderer :content="content" />
      </motion.div>
    </AnimatePresence>
  </div>
</template>
