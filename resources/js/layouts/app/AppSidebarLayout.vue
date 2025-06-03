<script setup lang="ts">
import type { BreadcrumbItemType, ChatHistory } from '@/types'
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'

interface Props {
  breadcrumbs?: BreadcrumbItemType[]
  chatHistory?: ChatHistory
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})
</script>

<template>
  <AppShell variant="sidebar">
    <AppSidebar :chat-history="chatHistory" />
    <AppContent variant="sidebar">
      <AppSidebarHeader :breadcrumbs="breadcrumbs" />
      <slot />
    </AppContent>
  </AppShell>
</template>
