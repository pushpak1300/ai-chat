<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Icon } from '@iconify/vue'

defineOptions({
  inheritAttrs: false,
})

defineProps<Props>()

interface Props {
  className?: HTMLAttributes['class']
}
</script>

<template>
  <Icon icon="lucide:sparkles" class="size-5" />
</template>
