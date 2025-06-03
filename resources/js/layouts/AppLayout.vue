<script setup lang="ts">
import type { BreadcrumbItemType, ChatHistory } from '@/types'
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'

interface Props {
  breadcrumbs?: BreadcrumbItemType[]
  chatHistory?: ChatHistory
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <slot />
  </AppLayout>
</template>
