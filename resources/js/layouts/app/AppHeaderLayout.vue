<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types'
import AppContent from '@/components/AppContent.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppShell from '@/components/AppShell.vue'

interface Props {
  breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})
</script>

<template>
  <AppShell class="flex-col">
    <AppHeader :breadcrumbs="breadcrumbs" />
    <AppContent>
      <slot />
    </AppContent>
  </AppShell>
</template>
